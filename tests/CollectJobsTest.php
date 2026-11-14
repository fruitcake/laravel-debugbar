<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

class CollectJobsTest extends TestCase
{
    use RefreshDatabase;

    private string $dir;

    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('debugbar.collect_jobs', true);
        $app['config']->set('debugbar.storage.enabled', true);
        $app['config']->set('debugbar.storage.path', storage_path('debugbar-jobs-test'));
        $app['config']->set('queue.default', 'database');
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->dir = storage_path('debugbar-jobs-test');
        File::deleteDirectory($this->dir);
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->dir);

        parent::tearDown();
    }

    public function testItStoresFailedJobsWithoutLeakingIntoTheNextJob()
    {
        $this->loadLaravelMigrations();
        $this->createJobsTable();

        dispatch(function () {
            debugbar()->addMessage('from failing job');
            throw new \RuntimeException('boom');
        });
        dispatch(function () {
            debugbar()->addMessage('from succeeding job');
        });

        $this->artisan('queue:work', ['--stop-when-empty' => true, '--tries' => 1]);

        $records = collect(debugbar()->getStorage()->find())
            ->map(fn($meta) => debugbar()->getStorage()->get($meta['id']))
            ->keyBy(fn($data) => array_column($data['messages']['messages'], 'message')[0]);

        static::assertCount(2, $records);

        $failed = $records['from failing job'];
        static::assertSame(1, $failed['exceptions']['count']);
        static::assertSame('boom', $failed['exceptions']['exceptions'][0]['message']);

        $succeeded = $records['from succeeding job'];
        static::assertSame(['from succeeding job'], array_column($succeeded['messages']['messages'], 'message'));
        static::assertSame(0, $succeeded['exceptions']['count']);
    }

    protected function createJobsTable()
    {
        (new class extends Migration {
            public function up()
            {
                if (Schema::hasTable('jobs')) {
                    return;
                }

                Schema::create('jobs', function (Blueprint $table) {
                    $table->bigIncrements('id');
                    $table->string('queue')->index();
                    $table->longText('payload');
                    $table->unsignedTinyInteger('attempts');
                    $table->unsignedInteger('reserved_at')->nullable();
                    $table->unsignedInteger('available_at');
                    $table->unsignedInteger('created_at');
                });
            }
        })->up();
    }
}
