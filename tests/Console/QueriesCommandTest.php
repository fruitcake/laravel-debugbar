<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Console;

use DebugBar\Storage\StorageInterface;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class QueriesCommandTest extends TestCase
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

    public function testQueriesCommandShowsNoQueriesMessage(): void
    {
        $this->setupStorage([], ['abc123' => ['queries' => []]]);

        Artisan::call('debugbar:queries', ['id' => 'abc123']);
        static::assertStringContainsString('No queries found', Artisan::output());
    }

    public function testQueriesCommandShowsSummaryTable(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 3,
                    'accumulated_duration_str' => '15ms',
                    'nb_failed_statements' => 0,
                    'statements' => [
                        ['sql' => 'select * from users', 'type' => 'query', 'connection' => 'mysql', 'duration_str' => '5ms', 'slow' => false, 'filename' => 'UserController.php'],
                        ['sql' => 'select * from posts', 'type' => 'query', 'connection' => 'mysql', 'duration_str' => '3ms', 'slow' => false, 'filename' => 'PostController.php'],
                        ['sql' => 'select count(*) from users', 'type' => 'query', 'connection' => 'mysql', 'duration_str' => '7ms', 'slow' => false, 'filename' => 'UserController.php'],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123']);
        $output = Artisan::output();

        static::assertStringContainsString('3 statements', $output);
        static::assertStringContainsString('15ms total', $output);
        static::assertStringContainsString('select * from users', $output);
        static::assertStringContainsString('select * from posts', $output);
    }

    public function testQueriesCommandResolvesLatest(): void
    {
        $this->setupStorage(
            [['id' => 'latest-id']],
            ['latest-id' => [
                'queries' => [
                    'nb_statements' => 1,
                    'accumulated_duration_str' => '5ms',
                    'nb_failed_statements' => 0,
                    'statements' => [
                        ['sql' => 'select 1', 'type' => 'query', 'connection' => 'mysql', 'duration_str' => '5ms', 'slow' => false, 'filename' => 'test.php'],
                    ],
                ],
            ]],
        );

        Artisan::call('debugbar:queries', ['id' => 'latest']);
        static::assertStringContainsString('latest-id', Artisan::output());
    }

    public function testQueriesCommandDetectsDuplicates(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 3,
                    'accumulated_duration_str' => '15ms',
                    'nb_failed_statements' => 0,
                    'statements' => [
                        ['sql' => 'select * from users where id = ?', 'params' => [1], 'type' => 'query', 'connection' => 'mysql', 'duration_str' => '5ms', 'slow' => false, 'filename' => 'a.php'],
                        ['sql' => 'select * from users where id = ?', 'params' => [1], 'type' => 'query', 'connection' => 'mysql', 'duration_str' => '5ms', 'slow' => false, 'filename' => 'b.php'],
                        ['sql' => 'select * from users where id = ?', 'params' => [1], 'type' => 'query', 'connection' => 'mysql', 'duration_str' => '5ms', 'slow' => false, 'filename' => 'c.php'],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123']);
        $output = Artisan::output();

        static::assertStringContainsString('3 duplicate queries in 1 group(s)', $output);
        static::assertStringContainsString('3x', $output);
    }

    public function testQueriesCommandShowsSlowQueryFlag(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 1,
                    'accumulated_duration_str' => '2s',
                    'nb_failed_statements' => 0,
                    'statements' => [
                        ['sql' => 'select * from big_table', 'type' => 'query', 'connection' => 'mysql', 'duration_str' => '2s', 'slow' => true, 'filename' => 'test.php'],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123']);
        static::assertStringContainsString('SLOW', Artisan::output());
    }

    public function testQueriesCommandShowsStatementDetail(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 1,
                    'accumulated_duration_str' => '5ms',
                    'nb_failed_statements' => 0,
                    'statements' => [
                        [
                            'sql' => 'select * from users where id = ?',
                            'params' => [42],
                            'type' => 'query',
                            'connection' => 'mysql',
                            'duration_str' => '5ms',
                            'slow' => false,
                            'filename' => 'UserController.php',
                            'backtrace' => [
                                ['index' => 0, 'name' => 'app/Http/Controllers/UserController.php', 'line' => 25],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123', '--statement' => 0]);
        $output = Artisan::output();

        static::assertStringContainsString('Statement #0', $output);
        static::assertStringContainsString('select * from users where id = ?', $output);
        static::assertStringContainsString('42', $output);
        static::assertStringContainsString('mysql', $output);
        static::assertStringContainsString('5ms', $output);
        static::assertStringContainsString('Backtrace', $output);
        static::assertStringContainsString('UserController.php', $output);
    }

    public function testQueriesCommandInvalidStatementIndex(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 1,
                    'accumulated_duration_str' => '5ms',
                    'nb_failed_statements' => 0,
                    'statements' => [
                        ['sql' => 'select 1', 'type' => 'query', 'connection' => 'mysql', 'duration_str' => '5ms', 'slow' => false, 'filename' => 'test.php'],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123', '--statement' => 99]);
        static::assertStringContainsString('Statement #99 not found', Artisan::output());
    }

    public function testQueriesCommandExplainRejectsNonSelect(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 1,
                    'accumulated_duration_str' => '5ms',
                    'nb_failed_statements' => 0,
                    'statements' => [
                        [
                            'sql' => 'delete from users where id = 1',
                            'type' => 'query',
                            'connection' => 'mysql',
                            'duration_str' => '5ms',
                            'slow' => false,
                            'filename' => 'test.php',
                            'explain' => ['connection' => 'mysql', 'query' => 'delete from users where id = 1'],
                        ],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123', '--statement' => 0, '--explain' => true]);
        static::assertStringContainsString('Only SELECT queries can be explained', Artisan::output());
    }

    public function testQueriesCommandResultRejectsNonSelect(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 1,
                    'accumulated_duration_str' => '5ms',
                    'nb_failed_statements' => 0,
                    'statements' => [
                        [
                            'sql' => 'update users set name = "test"',
                            'type' => 'query',
                            'connection' => 'mysql',
                            'duration_str' => '5ms',
                            'slow' => false,
                            'filename' => 'test.php',
                            'explain' => ['connection' => 'mysql', 'query' => 'update users set name = "test"'],
                        ],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123', '--statement' => 0, '--result' => true]);
        static::assertStringContainsString('Only SELECT queries can be executed', Artisan::output());
    }

    public function testQueriesCommandTruncatesLongSql(): void
    {
        $longSql = 'select ' . str_repeat('a', 200) . ' from users';

        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 1,
                    'accumulated_duration_str' => '5ms',
                    'nb_failed_statements' => 0,
                    'statements' => [
                        ['sql' => $longSql, 'type' => 'query', 'connection' => 'mysql', 'duration_str' => '5ms', 'slow' => false, 'filename' => 'test.php'],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123']);
        static::assertStringContainsString('...', Artisan::output());
    }
}
