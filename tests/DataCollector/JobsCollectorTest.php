<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use Fruitcake\LaravelDebugbar\Tests\Jobs\OrderShipped;
use Fruitcake\LaravelDebugbar\Tests\Jobs\SendNotification;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;

class JobsCollectorTest extends TestCase
{
    use RefreshDatabase;

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('debugbar.collectors.jobs', true);
        // The `sync` and `null` driver don't dispatch events
        // `database` or `redis` driver work great
        $app['config']->set('queue.default', 'database');

        parent::getEnvironmentSetUp($app);
    }

    public function testItCollectsDispatchedJobs()
    {
        $this->loadLaravelMigrations();
        $this->createJobsTable();

        debugbar()->boot();

        /** @var \DebugBar\DataCollector\ObjectCountCollector $collector */
        $collector = debugbar()->getCollector('jobs');
        $collector->setXdebugLinkTemplate('');
        $collector->setKeyMap([]);
        $data = [];

        static::assertEquals(
            [
                'data' => $data,
                'count' => 0,
                'is_counter' => true,
                'key_map' => [],
            ],
            $this->countsOnly($collector),
        );

        OrderShipped::dispatch(1);

        $data[OrderShipped::class] = ['value' => 1];
        static::assertEquals(
            [
                'data' => $data,
                'count' => 1,
                'is_counter' => true,
                'key_map' => [],
            ],
            $this->countsOnly($collector),
        );

        dispatch(new SendNotification());
        dispatch(new SendNotification());
        dispatch(new SendNotification());

        $data[SendNotification::class] = ['value' => 3];
        static::assertEquals(
            [
                'data' => $data,
                'count' => 4,
                'is_counter' => true,
                'key_map' => [],
            ],
            $this->countsOnly($collector),
        );
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

    /**
     * The collected data without its summary, which is asserted separately so these
     * exact-shape comparisons stay about the counting behaviour.
     */
    private function countsOnly(\DebugBar\DataCollector\ObjectCountCollector $collector): array
    {
        $data = $collector->collect();
        unset($data['summary']);

        return $data;
    }
}
