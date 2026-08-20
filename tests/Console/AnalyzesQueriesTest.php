<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Console;

use Fruitcake\LaravelDebugbar\Console\Concerns\AnalyzesQueries;
use PHPUnit\Framework\TestCase;

class AnalyzesQueriesTest extends TestCase
{
    use AnalyzesQueries;

    /** @return array{sql: string, type: string, connection: string} */
    private function stmt(string $sql, string $connection = 'mysql'): array
    {
        return ['sql' => $sql, 'type' => 'query', 'connection' => $connection];
    }

    public function testNormalizeStripsNumericLiterals(): void
    {
        static::assertSame(
            'select * from posts where user_id = ?',
            $this->normalizeSql('select * from posts where user_id = 42')
        );
    }

    public function testNormalizeStripsStringLiterals(): void
    {
        static::assertSame(
            'select * from users where email = ?',
            $this->normalizeSql("select * from users where email = 'a@b.com'")
        );
    }

    public function testNormalizeKeepsIdentifiersContainingDigits(): void
    {
        static::assertStringContainsString('posts2', $this->normalizeSql('select * from posts2 where id = 1'));
    }

    public function testNormalizeCollapsesInLists(): void
    {
        static::assertSame(
            $this->normalizeSql('select * from users where id in (1, 2, 3)'),
            $this->normalizeSql('select * from users where id in (7)')
        );
    }

    public function testNPlusOneGroupsQueriesThatDifferOnlyByLiteral(): void
    {
        $groups = $this->nPlusOneGroups([
            $this->stmt('select * from posts where user_id = 1'),
            $this->stmt('select * from posts where user_id = 2'),
            $this->stmt('select * from posts where user_id = 3'),
        ]);

        static::assertCount(1, $groups);
        static::assertSame([0, 1, 2], array_values($groups)[0]);
    }

    public function testExactRepeatsAreDuplicatesNotNPlusOne(): void
    {
        $statements = [
            $this->stmt('select * from users'),
            $this->stmt('select * from users'),
        ];

        static::assertCount(1, $this->duplicateGroups($statements));
        static::assertCount(0, $this->nPlusOneGroups($statements));
    }

    public function testDifferentConnectionsAreNotGrouped(): void
    {
        static::assertCount(0, $this->nPlusOneGroups([
            $this->stmt('select * from posts where user_id = 1', 'mysql'),
            $this->stmt('select * from posts where user_id = 2', 'pgsql'),
        ]));
    }

    public function testNonQueryStatementsAreIgnored(): void
    {
        static::assertCount(0, $this->nPlusOneGroups([
            ['sql' => 'Connection Established', 'type' => 'transaction', 'connection' => 'mysql'],
            ['sql' => 'Connection Established', 'type' => 'transaction', 'connection' => 'mysql'],
        ]));
    }

    public function testFailedStatementsAreKeyedByIndex(): void
    {
        $failed = $this->failedStatements([
            $this->stmt('select 1'),
            ['sql' => 'select * from nope', 'type' => 'query', 'is_success' => false, 'error_message' => 'boom'],
        ]);

        static::assertSame([1], array_keys($failed));
    }
}
