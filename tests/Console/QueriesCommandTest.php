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

    /** Info statements (query limit notices) carry no `slow` key. */
    public function testQueriesCommandHandlesStatementsWithoutSlowKey(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 1,
                    'accumulated_duration_str' => '1ms',
                    'statements' => [
                        ['sql' => '# Query soft limit reached', 'type' => 'info'],
                        ['sql' => 'select 1', 'type' => 'query', 'connection' => 'mysql', 'duration_str' => '1ms'],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123']);
        static::assertStringContainsString('select 1', Artisan::output());
    }

    public function testQueriesCommandSurfacesFailedQueries(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 1,
                    'accumulated_duration_str' => '1ms',
                    'nb_failed_statements' => 0,
                    'statements' => [
                        [
                            'sql' => 'select * from nope',
                            'type' => 'query',
                            'connection' => 'mysql',
                            'is_success' => false,
                            'error_code' => '42S02',
                            'error_message' => 'Base table or view not found',
                        ],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123']);
        $output = Artisan::output();

        static::assertStringContainsString('1 statements | 1ms total | 1 failed', $output);
        static::assertStringContainsString('FAILED', $output);
        static::assertStringContainsString('Base table or view not found', $output);
    }

    public function testQueriesCommandShowsErrorOnStatementDetail(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 1,
                    'statements' => [
                        ['sql' => 'select * from nope', 'type' => 'query', 'is_success' => false, 'error_code' => '42S02', 'error_message' => 'no such table'],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123', '--statement' => 0]);
        static::assertStringContainsString('FAILED: 42S02 no such table', Artisan::output());
    }

    public function testQueriesCommandRequiresStatementForExplain(): void
    {
        $this->setupStorage([], [
            'abc123' => ['queries' => ['nb_statements' => 1, 'statements' => [['sql' => 'select 1', 'type' => 'query']]]],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123', '--explain' => true]);
        static::assertStringContainsString('require --statement=N', Artisan::output());
    }

    public function testQueriesCommandHandlesLatestOnEmptyStorage(): void
    {
        $this->setupStorage([], []);

        Artisan::call('debugbar:queries', ['id' => 'latest']);
        static::assertStringContainsString('No requests in the Debugbar Storage yet', Artisan::output());
    }

    public function testQueriesCommandHandlesUnknownId(): void
    {
        $this->setupStorage([], []);

        Artisan::call('debugbar:queries', ['id' => 'nope']);
        static::assertStringContainsString('Request nope not found', Artisan::output());
    }

    public function testQueriesCommandHandlesNoStorage(): void
    {
        $debugbar = app(LaravelDebugbar::class);
        $debugbar->boot();
        $debugbar->setStorage(null);

        Artisan::call('debugbar:queries', ['id' => 'latest']);
        static::assertStringContainsString('No Debugbar Storage found', Artisan::output());
    }

    public function testQueriesCommandJsonSummary(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 3,
                    'accumulated_duration' => 0.015,
                    'accumulated_duration_str' => '15ms',
                    'statements' => [
                        ['sql' => 'select * from users where id = ?', 'params' => [1], 'type' => 'query', 'connection' => 'mysql', 'duration_str' => '5ms', 'filename' => 'UserController.php:10'],
                        ['sql' => 'select * from users where id = ?', 'params' => [1], 'type' => 'query', 'connection' => 'mysql', 'duration_str' => '5ms'],
                        ['sql' => 'select 1', 'type' => 'query', 'connection' => 'mysql', 'slow' => true, 'duration_str' => '5ms'],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123', '--json' => true]);
        $decoded = json_decode(Artisan::output(), true);

        static::assertSame(3, $decoded['nb_statements']);
        static::assertCount(1, $decoded['duplicate_groups']);
        static::assertSame(2, $decoded['duplicate_groups'][0]['count']);
        static::assertSame([0, 1], $decoded['duplicate_groups'][0]['statements']);
        static::assertSame(2, $decoded['statements'][0]['duplicates']);
        static::assertTrue($decoded['statements'][2]['slow']);
        static::assertSame('UserController.php:10', $decoded['statements'][0]['source']);
    }

    public function testQueriesCommandJsonStatementDetail(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 1,
                    'statements' => [['sql' => 'select 1', 'type' => 'query', 'connection' => 'mysql']],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123', '--statement' => 0, '--json' => true]);
        $decoded = json_decode(Artisan::output(), true);

        static::assertSame('select 1', $decoded['sql']);
    }

    /** The classic N+1: same SQL, different binding each time, so never an exact duplicate. */
    public function testQueriesCommandDetectsNPlusOne(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 3,
                    'statements' => [
                        ['sql' => 'select * from posts where user_id = ?', 'params' => [1], 'type' => 'query', 'connection' => 'mysql'],
                        ['sql' => 'select * from posts where user_id = ?', 'params' => [2], 'type' => 'query', 'connection' => 'mysql'],
                        ['sql' => 'select * from posts where user_id = ?', 'params' => [3], 'type' => 'query', 'connection' => 'mysql'],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123']);
        $output = Artisan::output();

        static::assertStringContainsString('repeated query shape(s) with varying bindings', $output);
        static::assertStringContainsString('3x', $output);
    }

    public function testQueriesCommandDoesNotReportExactDuplicatesAsNPlusOne(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 2,
                    'statements' => [
                        ['sql' => 'select * from users', 'type' => 'query', 'connection' => 'mysql'],
                        ['sql' => 'select * from users', 'type' => 'query', 'connection' => 'mysql'],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123']);
        $output = Artisan::output();

        static::assertStringContainsString('duplicate queries in 1 group(s)', $output);
        static::assertStringNotContainsString('varying bindings', $output);
    }

    public function testQueriesCommandJsonIncludesNPlusOneGroups(): void
    {
        $this->setupStorage([], [
            'abc123' => [
                'queries' => [
                    'nb_statements' => 2,
                    'statements' => [
                        ['sql' => 'select * from posts where user_id = ?', 'params' => [1], 'type' => 'query', 'connection' => 'mysql'],
                        ['sql' => 'select * from posts where user_id = ?', 'params' => [2], 'type' => 'query', 'connection' => 'mysql'],
                    ],
                ],
            ],
        ]);

        Artisan::call('debugbar:queries', ['id' => 'abc123', '--json' => true]);
        $decoded = json_decode(Artisan::output(), true);

        static::assertCount(1, $decoded['n_plus_one_groups']);
        static::assertSame(2, $decoded['n_plus_one_groups'][0]['count']);
        static::assertSame([], $decoded['duplicate_groups']);
    }
}
