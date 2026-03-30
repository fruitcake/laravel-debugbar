<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Console;

use DebugBar\Storage\StorageInterface;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class FindCommandTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('debugbar.enabled', true);
        $app['config']->set('debugbar.storage.enabled', true);
    }

    private function setupStorage(array $findResult, array $getResults = []): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('find')->willReturn($findResult);
        $storage->method('get')->willReturnCallback(fn(string $id) => $getResults[$id] ?? []);

        $debugbar = app(LaravelDebugbar::class);
        $debugbar->boot();
        $debugbar->setStorage($storage);
    }

    private function makeRequestRow(string $id, string $uri = '/test', string $method = 'GET'): array
    {
        return ['id' => $id, 'uri' => $uri, 'method' => $method, 'ip' => '127.0.0.1', 'utime' => 1234567890];
    }

    private function makeRequestData(array $overrides = []): array
    {
        return array_merge([
            'exceptions' => ['count' => 0],
            'queries' => [
                'nb_statements' => 1,
                'accumulated_duration_str' => '5ms',
                'statements' => [],
                'nb_failed_statements' => 0,
            ],
            'time' => ['duration' => 0.05, 'duration_str' => '50ms'],
            'memory' => ['peak_usage_str' => '2MB'],
            '__meta' => ['status' => '200'],
            'request' => ['tooltip' => ['status' => '200']],
        ], $overrides);
    }

    public function testFindCommandHandlesNoStorage(): void
    {
        $debugbar = app(LaravelDebugbar::class);
        $debugbar->boot();
        $debugbar->setStorage(null);

        Artisan::call('debugbar:find');
        static::assertStringContainsString('No Debugbar Storage found', Artisan::output());
    }

    public function testFindCommandShowsNoResultsMessage(): void
    {
        $this->setupStorage([]);

        Artisan::call('debugbar:find');
        static::assertStringContainsString('No results found', Artisan::output());
    }

    public function testFindCommandListsRequests(): void
    {
        $this->setupStorage(
            [$this->makeRequestRow('abc123', '/test')],
            ['abc123' => $this->makeRequestData([
                'time' => ['duration_str' => '120ms'],
                'memory' => ['peak_usage_str' => '4MB'],
                'queries' => ['nb_statements' => 5, 'accumulated_duration_str' => '10ms'],
            ])],
        );

        Artisan::call('debugbar:find');
        $output = Artisan::output();

        static::assertStringContainsString('abc123', $output);
        static::assertStringContainsString('/test', $output);
        static::assertStringContainsString('120ms/4MB request', $output);
        static::assertStringContainsString('5 queries in 10ms', $output);
    }

    public function testFindCommandPassesFilters(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())
            ->method('find')
            ->with(
                static::callback(fn(array $filters) => $filters['method'] === 'POST' && $filters['uri'] === '/api/*'),
                static::equalTo(20),
                static::equalTo(0),
            )
            ->willReturn([]);

        $debugbar = app(LaravelDebugbar::class);
        $debugbar->boot();
        $debugbar->setStorage($storage);

        Artisan::call('debugbar:find', ['--method' => 'POST', '--uri' => '/api/*']);
    }

    public function testFindCommandMaxAndOffsetOptions(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())
            ->method('find')
            ->with([], 50, 10)
            ->willReturn([]);

        $debugbar = app(LaravelDebugbar::class);
        $debugbar->boot();
        $debugbar->setStorage($storage);

        Artisan::call('debugbar:find', ['--max' => 50, '--offset' => 10]);
    }

    public function testFindCommandIssuesFilterShowsExceptions(): void
    {
        $this->setupStorage(
            [$this->makeRequestRow('req1', '/err')],
            ['req1' => $this->makeRequestData(['exceptions' => ['count' => 2]])],
        );

        Artisan::call('debugbar:find', ['--issues' => true]);
        static::assertStringContainsString('2 exception(s)', Artisan::output());
    }

    public function testFindCommandIssuesFilterShowsHttpErrors(): void
    {
        $this->setupStorage(
            [$this->makeRequestRow('req2', '/fail')],
            ['req2' => $this->makeRequestData(['__meta' => ['status' => '500']])],
        );

        Artisan::call('debugbar:find', ['--issues' => true]);
        static::assertStringContainsString('HTTP 500', Artisan::output());
    }

    public function testFindCommandIssuesFilterShowsHighQueryCount(): void
    {
        $this->setupStorage(
            [$this->makeRequestRow('req3', '/heavy')],
            ['req3' => $this->makeRequestData([
                'queries' => ['nb_statements' => 60, 'accumulated_duration_str' => '500ms', 'statements' => [], 'nb_failed_statements' => 0],
            ])],
        );

        Artisan::call('debugbar:find', ['--issues' => true]);
        static::assertStringContainsString('60 queries', Artisan::output());
    }

    public function testFindCommandIssuesFilterShowsSlowDuration(): void
    {
        $this->setupStorage(
            [$this->makeRequestRow('req4', '/slow')],
            ['req4' => $this->makeRequestData([
                'time' => ['duration' => 2.5, 'duration_str' => '2.5s'],
            ])],
        );

        Artisan::call('debugbar:find', ['--issues' => true]);
        static::assertStringContainsString('slow', Artisan::output());
    }

    public function testFindCommandIssuesFilterShowsDuplicateQueries(): void
    {
        $this->setupStorage(
            [$this->makeRequestRow('req5', '/dup')],
            ['req5' => $this->makeRequestData([
                'queries' => [
                    'nb_statements' => 3,
                    'accumulated_duration_str' => '15ms',
                    'nb_failed_statements' => 0,
                    'statements' => [
                        ['sql' => 'select * from users', 'type' => 'query', 'connection' => 'mysql'],
                        ['sql' => 'select * from users', 'type' => 'query', 'connection' => 'mysql'],
                        ['sql' => 'select * from users', 'type' => 'query', 'connection' => 'mysql'],
                    ],
                ],
            ])],
        );

        Artisan::call('debugbar:find', ['--issues' => true, '--min-duplicates' => 1]);
        static::assertStringContainsString('duplicate group(s)', Artisan::output());
    }

    public function testFindCommandIssuesFilterShowsSlowQueries(): void
    {
        $this->setupStorage(
            [$this->makeRequestRow('req6', '/sq')],
            ['req6' => $this->makeRequestData([
                'queries' => [
                    'nb_statements' => 1,
                    'accumulated_duration_str' => '5ms',
                    'nb_failed_statements' => 0,
                    'statements' => [
                        ['sql' => 'select * from big_table', 'type' => 'query', 'slow' => true],
                    ],
                ],
            ])],
        );

        Artisan::call('debugbar:find', ['--issues' => true]);
        static::assertStringContainsString('1 slow query', Artisan::output());
    }

    public function testFindCommandIssuesFilterShowsFailedQueries(): void
    {
        $this->setupStorage(
            [$this->makeRequestRow('req7', '/fq')],
            ['req7' => $this->makeRequestData([
                'queries' => [
                    'nb_statements' => 1,
                    'accumulated_duration_str' => '5ms',
                    'nb_failed_statements' => 1,
                    'statements' => [],
                ],
            ])],
        );

        Artisan::call('debugbar:find', ['--issues' => true]);
        static::assertStringContainsString('1 failed query', Artisan::output());
    }

    public function testFindCommandNoIssuesFoundMessage(): void
    {
        $this->setupStorage(
            [$this->makeRequestRow('req8', '/ok')],
            ['req8' => $this->makeRequestData()],
        );

        Artisan::call('debugbar:find', ['--issues' => true]);
        static::assertStringContainsString('No issues found', Artisan::output());
    }

    public function testFindCommandCustomThresholds(): void
    {
        $this->setupStorage(
            [$this->makeRequestRow('req9', '/ct')],
            ['req9' => $this->makeRequestData([
                'queries' => ['nb_statements' => 15, 'accumulated_duration_str' => '50ms', 'statements' => [], 'nb_failed_statements' => 0],
            ])],
        );

        Artisan::call('debugbar:find', ['--min-queries' => 10]);
        static::assertStringContainsString('15 queries', Artisan::output());
    }

    public function testFindCommandShowsExceptionCountInSummary(): void
    {
        $this->setupStorage(
            [$this->makeRequestRow('req10', '/ex')],
            ['req10' => $this->makeRequestData(['exceptions' => ['count' => 1]])],
        );

        Artisan::call('debugbar:find');
        static::assertStringContainsString('1 exception(s)', Artisan::output());
    }
}
