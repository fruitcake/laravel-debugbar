<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Console;

use DebugBar\Storage\FileStorage;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Exercises the console commands against data the collectors really produced,
 * rather than hand written fixtures, so the commands stay in sync with the
 * actual output shape.
 *
 * Note that `runningInConsole()` is true under PHPUnit, so the request collector
 * always takes its CLI branch here and no HTTP status is available. Status code
 * handling is covered by the fixture based tests in FindCommandTest.
 */
class ConsoleIntegrationTest extends TestCase
{
    private string $dir;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('debugbar.enabled', true);
        $app['config']->set('debugbar.storage.enabled', true);

        // These tests only care about the query collector, and reporting an
        // exception would otherwise pull in the message collectors too.
        $app['config']->set('debugbar.collectors.messages', false);
        $app['config']->set('debugbar.collectors.log', false);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = storage_path('debugbar-console-test');
        File::deleteDirectory($this->dir);
        File::ensureDirectoryExists($this->dir);

        $debugbar = app(LaravelDebugbar::class);
        $debugbar->enable();
        $debugbar->boot();
        $debugbar->setStorage(new FileStorage($this->dir));
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    /** Run the work the collectors should see, then store and return the id. */
    private function capture(callable $work): string
    {
        $work();

        $debugbar = app(LaravelDebugbar::class);
        $debugbar->collect();

        $found = $debugbar->getStorage()->find([], 1);
        static::assertNotEmpty($found, 'Nothing was written to the storage');

        return $found[0]['id'];
    }

    public function testDetectsNPlusOneInRealCollectedQueries(): void
    {
        DB::statement('create table console_users (id integer primary key, name text)');
        DB::statement('create table console_posts (id integer primary key, user_id integer)');
        DB::insert('insert into console_users (id, name) values (1, ?), (2, ?), (3, ?)', ['a', 'b', 'c']);

        $id = $this->capture(function () {
            // A deliberate N+1: same SQL, a different binding each time.
            foreach (DB::select('select * from console_users') as $user) {
                DB::select('select * from console_posts where user_id = ?', [$user->id]);
            }

            return 'ok';
        });

        Artisan::call('debugbar:queries', ['id' => $id, '--json' => true]);
        $queries = json_decode(Artisan::output(), true);
        static::assertCount(1, $queries['n_plus_one_groups'], 'the per-user lookup should be one N+1 group');
        static::assertSame(3, $queries['n_plus_one_groups'][0]['count']);
        static::assertStringContainsString('console_posts', $queries['n_plus_one_groups'][0]['sql']);
        // The collector renders bindings into the SQL, so the three statements differ
        // textually. Only literal-stripping normalization groups them.
        static::assertStringContainsString('user_id = 1', $queries['statements'][5]['sql']);
        static::assertSame([], $queries['duplicate_groups'], 'differing bindings are not exact duplicates');

        Artisan::call('debugbar:find', ['--issues' => true, '--min-duplicates' => 1]);
        static::assertStringContainsString('N+1 group(s)', Artisan::output());
    }

    public function testSurfacesFailedQueriesFromRealCollectedData(): void
    {
        $id = $this->capture(function () {
            try {
                DB::select('select * from table_that_does_not_exist');
            } catch (\Throwable $e) {
                // The collector hooks the exception reporter, not the catch site.
                report($e);
            }

            return 'ok';
        });

        Artisan::call('debugbar:queries', ['id' => $id]);
        $output = Artisan::output();

        static::assertStringContainsString('1 failed', $output);
        static::assertStringContainsString('FAILED', $output);
        static::assertStringContainsString('table_that_does_not_exist', $output);

        // nb_failed_statements is always 0 in the collected data, so this only
        // works because the statements themselves are inspected.
        Artisan::call('debugbar:find', ['--issues' => true]);
        static::assertStringContainsString('1 failed query', Artisan::output());
    }

    public function testGetSummaryRendersWithoutDumpingArrays(): void
    {
        $id = $this->capture(fn() => DB::select('select 1'));

        Artisan::call('debugbar:get', ['id' => $id]);
        $output = Artisan::output();

        static::assertStringContainsString($id, $output);
        static::assertStringNotContainsString('array:', $output, 'collector summaries must be single line');
    }

    public function testHandlesTheTransactionStatementsThatCarryNoSlowKey(): void
    {
        $id = $this->capture(function () {
            DB::statement('create table console_tx (id integer primary key)');

            return 'ok';
        });

        // Connection/transaction statements have no `slow` key at all.
        Artisan::call('debugbar:queries', ['id' => $id]);
        static::assertStringContainsString('console_tx', Artisan::output());
    }
}
