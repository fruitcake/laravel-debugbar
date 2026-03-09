<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;

class QueryCollectorTest extends TestCase
{
    use RefreshDatabase;

    public function testItReplacesQuestionMarksBindingsCorrectly()
    {
        $this->loadLaravelMigrations();

        debugbar()->boot();

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\QueryCollector $collector */
        $collector  = debugbar()->getCollector('queries');
        $collector->addQuery(new QueryExecuted(
            "SELECT ('[1, 2, 3]'::jsonb ?? ?) as a, ('[4, 5, 6]'::jsonb ??| ?) as b, 'hello world ? example ??' as c",
            [3, '{4}'],
            0,
            $this->app['db']->connection(),
        ));

        tap($collector->collect(), function (array $collection) {
            $this->assertEquals(1, $collection['nb_statements']);

            tap(Arr::first($collection['statements']), function (array $statement) {
                $this->assertEquals([3, '{4}'], $statement['params']);
                $this->assertEquals(<<<SQL
                    SELECT ('[1, 2, 3]'::jsonb ? 3) as a, ('[4, 5, 6]'::jsonb ?| '{4}') as b, 'hello world ? example ??' as c
                    SQL, $statement['sql']);
            });
        });
    }

    public function testDollarBindingsArePresentedCorrectly()
    {
        debugbar()->boot();

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\QueryCollector $collector */
        $collector = debugbar()->getCollector('queries');
        $collector->addQuery(new QueryExecuted(
            "SELECT a FROM b WHERE c = ? AND d = ? AND e = ?",
            ['$10', '$2y$10_DUMMY_BCRYPT_HASH', '$_$$_$$$_$2_$3'],
            0,
            $this->app['db']->connection(),
        ));

        tap(Arr::first($collector->collect()['statements']), function (array $statement) {
            $this->assertEquals(
                "SELECT a FROM b WHERE c = '$10' AND d = '$2y$10_DUMMY_BCRYPT_HASH' AND e = '\$_$\$_$$\$_$2_$3'",
                $statement['sql'],
            );
        });
    }

    public function testExplainModesForSelectQueryWithSupportedDriver(): void
    {
        debugbar()->boot();

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\QueryCollector $collector */
        $collector = debugbar()->getCollector('queries');
        $collector->setExplainSource(true);
        $collector->addQuery(new QueryExecuted(
            'SELECT * FROM users WHERE id = ?',
            [1],
            0,
            $this->app['db']->connection(),
        ));

        tap(Arr::first($collector->collect()['statements']), function (array $statement) {
            // SQLite doesn't support raw explain, so only 'result' mode
            $this->assertNotNull($statement['explain']);
            $this->assertContains('result', $statement['explain']['modes']);
        });
    }

    public function testExplainModesNullForNonSelectQuery(): void
    {
        debugbar()->boot();

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\QueryCollector $collector */
        $collector = debugbar()->getCollector('queries');
        $collector->setExplainSource(true);
        $collector->addQuery(new QueryExecuted(
            'INSERT INTO users (name) VALUES (?)',
            ['test'],
            0,
            $this->app['db']->connection(),
        ));

        tap(Arr::first($collector->collect()['statements']), function (array $statement) {
            $this->assertNull($statement['explain']);
        });
    }

    public function testExplainModesNullWhenExplainDisabled(): void
    {
        debugbar()->boot();

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\QueryCollector $collector */
        $collector = debugbar()->getCollector('queries');
        $collector->setExplainSource(false);
        $collector->addQuery(new QueryExecuted(
            'SELECT * FROM users',
            [],
            0,
            $this->app['db']->connection(),
        ));

        tap(Arr::first($collector->collect()['statements']), function (array $statement) {
            $this->assertNull($statement['explain']);
        });
    }

    public function testExplainModesForWithQuery(): void
    {
        debugbar()->boot();

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\QueryCollector $collector */
        $collector = debugbar()->getCollector('queries');
        $collector->setExplainSource(true);
        $collector->addQuery(new QueryExecuted(
            'WITH cte AS (SELECT 1) SELECT * FROM cte',
            [],
            0,
            $this->app['db']->connection(),
        ));

        tap(Arr::first($collector->collect()['statements']), function (array $statement) {
            $this->assertNotNull($statement['explain']);
            $this->assertContains('result', $statement['explain']['modes']);
        });
    }

    public function testExplainModesExcludeExplainForSqlite(): void
    {
        debugbar()->boot();

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\QueryCollector $collector */
        $collector = debugbar()->getCollector('queries');
        $collector->setExplainSource(true);
        $collector->addQuery(new QueryExecuted(
            'SELECT * FROM users',
            [],
            0,
            $this->app['db']->connection(),
        ));

        tap(Arr::first($collector->collect()['statements']), function (array $statement) {
            $this->assertNotNull($statement['explain']);
            $this->assertNotContains('explain', $statement['explain']['modes']);
        });
    }

    public function testExplainModesExcludeExplainWhenBindingsNull(): void
    {
        debugbar()->boot();

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\QueryCollector $collector */
        $collector = debugbar()->getCollector('queries');
        $collector->setExplainSource(true);
        $collector->setLimits(0, null);
        $collector->addQuery(new QueryExecuted(
            'SELECT * FROM users WHERE id = ?',
            [1],
            0,
            $this->app['db']->connection(),
        ));

        tap(Arr::first($collector->collect()['statements']), function (array $statement) {
            // Bindings are null due to soft limit, so 'explain' mode should not be present
            // But 'result' should still be there for read-only queries
            $this->assertNotNull($statement['explain']);
            $this->assertContains('result', $statement['explain']['modes']);
        });
    }

    public function testFindingCorrectPathForView()
    {
        debugbar()->boot();

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\QueryCollector $collector */
        $collector = debugbar()->getCollector('queries');

        view('query')
            ->with('db', $this->app['db']->connection())
            ->with('collector', $collector)
            ->render();

        tap(Arr::first($collector->collect()['statements']), function (array $statement) {
            $this->assertEquals(
                "SELECT a FROM b WHERE c = '$10' AND d = '$2y$10_DUMMY_BCRYPT_HASH' AND e = '\$_$\$_$$\$_$2_$3'",
                $statement['sql'],
            );

            $this->assertTrue(@file_exists($statement['backtrace'][1]->file));
            $this->assertEquals(
                realpath(__DIR__ . '/../resources/views/query.blade.php'),
                realpath($statement['backtrace'][1]->file),
            );
        });
    }
}
