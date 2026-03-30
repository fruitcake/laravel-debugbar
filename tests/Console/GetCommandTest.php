<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Console;

use DebugBar\Storage\StorageInterface;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class GetCommandTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('debugbar.enabled', true);
        $app['config']->set('debugbar.storage.enabled', true);
    }

    private function setupStorage(array $findResult = [], array $getData = []): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('find')->willReturn($findResult);
        $storage->method('get')->willReturnCallback(fn(string $id) => $getData[$id] ?? []);

        $debugbar = app(LaravelDebugbar::class);
        $debugbar->boot();
        $debugbar->setStorage($storage);
    }

    public function testGetCommandShowsSummary(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                '__meta' => ['id' => 'abc123', 'uri' => '/test', 'method' => 'GET', 'status' => '200'],
                'time' => ['duration_str' => '120ms'],
                'memory' => ['peak_usage_str' => '4MB'],
                'queries' => [
                    'nb_statements' => 5,
                    'accumulated_duration_str' => '10ms',
                ],
            ],
        ]);

        Artisan::call('debugbar:get', ['id' => 'abc123']);
        $output = Artisan::output();

        static::assertStringContainsString('abc123', $output);
        static::assertStringContainsString('120ms', $output);
        static::assertStringContainsString('5 queries in 10ms', $output);
    }

    public function testGetCommandResolvesLatest(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())
            ->method('find')
            ->with([], 1)
            ->willReturn([['id' => 'latest-id']]);
        $storage->method('get')->willReturn([
            '__meta' => ['id' => 'latest-id', 'uri' => '/latest', 'method' => 'GET', 'status' => '200'],
            'time' => ['duration_str' => '50ms'],
        ]);

        $debugbar = app(LaravelDebugbar::class);
        $debugbar->boot();
        $debugbar->setStorage($storage);

        Artisan::call('debugbar:get', ['id' => 'latest']);
        static::assertStringContainsString('latest-id', Artisan::output());
    }

    public function testGetCommandShowsSpecificCollector(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                '__meta' => ['id' => 'abc123'],
                'exceptions' => [
                    'count' => 1,
                    'exceptions' => [
                        ['message' => 'Test exception', 'type' => 'RuntimeException'],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:get', ['id' => 'abc123', '--collector' => 'exceptions', '--raw' => true]);
        $output = Artisan::output();

        static::assertStringContainsString('Test exception', $output);
        static::assertStringContainsString('RuntimeException', $output);
    }

    public function testGetCommandCollectorNotFound(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                '__meta' => ['id' => 'abc123'],
            ],
        ]);

        Artisan::call('debugbar:get', ['id' => 'abc123', '--collector' => 'nonexistent']);
        static::assertStringContainsString('No data found for collector nonexistent', Artisan::output());
    }

    public function testGetCommandRawOutput(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                '__meta' => ['id' => 'abc123', 'uri' => '/test', 'method' => 'GET'],
                'time' => ['duration_str' => '50ms'],
            ],
        ]);

        Artisan::call('debugbar:get', ['id' => 'abc123', '--raw' => true]);
        $output = Artisan::output();

        static::assertStringContainsString('"__meta"', $output);
        static::assertStringContainsString('"duration_str"', $output);
        static::assertStringContainsString('50ms', $output);
    }

    public function testGetCommandRawOutputWithCollector(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                '__meta' => ['id' => 'abc123'],
                'queries' => [
                    'nb_statements' => 3,
                    'accumulated_duration_str' => '15ms',
                ],
            ],
        ]);

        Artisan::call('debugbar:get', ['id' => 'abc123', '--collector' => 'queries', '--raw' => true]);
        $output = Artisan::output();

        static::assertStringContainsString('"nb_statements"', $output);
        static::assertStringContainsString('3', $output);
    }
}
