<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Database\Connection;

class QueryCollectorRuntimeDatabaseTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', null);
        $app['config']->set('database.connections', []);
        $app['config']->set('debugbar.options.db.explain.enabled', false);
    }

    public function testCollectsQueriesFromRuntimeConnections()
    {
        if (version_compare($this->app->version(), '10', '<')) {
            static::markTestSkipped('This test is not compatible with Laravel 9.x and below');
        }

        debugbar()->enable();

        /** @var Connection $connection */
        $connection = $this->app['db']->connectUsing(
            'runtime-connection',
            [
                'driver' => 'sqlite',
                'database' => ':memory:',
            ],
        );

        $connection->statement('SELECT 1');

        /** @var \Debugbar\DataCollector\ExceptionsCollector $collector */
        $exceptions = debugbar()->getCollector('exceptions');

        static::assertEmpty($exceptions->getExceptions());

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\QueryCollector $collector */
        $collector  = debugbar()->getCollector('queries');

        tap($collector->collect(), function (array $collection) {
            $this->assertEquals(1, $collection['nb_statements']);

            self::assertSame('SELECT 1', $collection['statements'][1]['sql']);
        });
    }

    public function testCollectsQueriesFromRuntimeConnectionsWithoutConnectUsing()
    {
        debugbar()->enable();

        $this->app['config']->set('database.connections.dynamic-connection', [
            'driver' => 'sqlite',
            'database' => ':memory:',
        ]);

        $this->app['config']->set('database.default', 'dynamic-connection');

        /** @var Connection $connection */
        $connection = $this->app['db']->connection('dynamic-connection');

        $connection->statement('SELECT 1');

        /** @var \Debugbar\DataCollector\ExceptionsCollector $collector */
        $exceptions = debugbar()->getCollector('exceptions');

        static::assertEmpty($exceptions->getExceptions());

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\QueryCollector $collector */
        $collector  = debugbar()->getCollector('queries');

        tap($collector->collect(), function (array $collection) {
            $this->assertEquals(1, $collection['nb_statements']);

            self::assertSame('SELECT 1', $collection['statements'][1]['sql']);
        });
    }
}
