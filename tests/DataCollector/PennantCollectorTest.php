<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use Fruitcake\LaravelDebugbar\DataCollector\PennantCollector;
use Fruitcake\LaravelDebugbar\ServiceProvider;
use Fruitcake\LaravelDebugbar\Tests\TestCase;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Pennant\Feature;
use Laravel\Pennant\PennantServiceProvider;

class PennantCollectorTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        if (! class_exists(PennantServiceProvider::class)) {
            static::markTestSkipped('Pennant is not installed.');
        }
    }

    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class, PennantServiceProvider::class];
    }

    /**
     * Define database migrations.
     *
     * @return void
     */
    protected function defineDatabaseMigrations()
    {
        $reflection = new \ReflectionClass(Feature::class);
        // Load Pennant migrations
        $this->loadMigrationsFrom(
            dirname($reflection->getFileName()) . '/../database/migrations'
        );
    }

    public function testItCollectsPennantValues()
    {
        debugbar()->boot();

        Feature::define('new-api', true);
        Feature::define('old-api', fn() => false);
        Feature::define('api-version', fn() => '3.x');

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\GateCollector $collector */
        $collector = debugbar()->getCollector('pennant');

        static::assertInstanceOf(PennantCollector::class, $collector);
        $data = $collector->collect();

        static::assertCount(3, $data);
        static::assertTrue($data['new-api']);
        static::assertFalse($data['old-api']);
        static::assertEquals('3.x', $data['api-version']);
    }
}
