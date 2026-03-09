<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Support;

use Fruitcake\LaravelDebugbar\Support\Explain;
use PHPUnit\Framework\TestCase;

class ExplainTest extends TestCase
{
    private Explain $explain;

    protected function setUp(): void
    {
        parent::setUp();

        $this->explain = new Explain();
    }

    public function testSelectQueryIsReadOnly(): void
    {
        static::assertTrue($this->explain->isReadOnlyQuery('SELECT * FROM users'));
    }

    public function testSelectQueryWithLeadingWhitespaceIsReadOnly(): void
    {
        static::assertTrue($this->explain->isReadOnlyQuery('  SELECT * FROM users'));
    }

    public function testSelectQueryCaseInsensitiveIsReadOnly(): void
    {
        static::assertTrue($this->explain->isReadOnlyQuery('select * FROM users'));
    }

    public function testWithQueryIsReadOnly(): void
    {
        static::assertTrue($this->explain->isReadOnlyQuery('WITH cte AS (SELECT 1) SELECT * FROM cte'));
    }

    public function testWithQueryCaseInsensitiveIsReadOnly(): void
    {
        static::assertTrue($this->explain->isReadOnlyQuery('with cte AS (SELECT 1) SELECT * FROM cte'));
    }

    public function testInsertQueryIsNotReadOnly(): void
    {
        static::assertFalse($this->explain->isReadOnlyQuery('INSERT INTO users (name) VALUES (?)'));
    }

    public function testUpdateQueryIsNotReadOnly(): void
    {
        static::assertFalse($this->explain->isReadOnlyQuery('UPDATE users SET name = ? WHERE id = ?'));
    }

    public function testDeleteQueryIsNotReadOnly(): void
    {
        static::assertFalse($this->explain->isReadOnlyQuery('DELETE FROM users WHERE id = ?'));
    }

    public function testDropQueryIsNotReadOnly(): void
    {
        static::assertFalse($this->explain->isReadOnlyQuery('DROP TABLE users'));
    }

    public function testSelectAsSubstringIsNotReadOnly(): void
    {
        static::assertFalse($this->explain->isReadOnlyQuery('SELECTFOO'));
    }

    public function testWithAsSubstringIsNotReadOnly(): void
    {
        static::assertFalse($this->explain->isReadOnlyQuery('WITHFOO'));
    }

    public function testRawExplainSupportedForMysql(): void
    {
        static::assertTrue($this->explain->isRawExplainSupported('mysql', []));
    }

    public function testRawExplainSupportedForMariadb(): void
    {
        static::assertTrue($this->explain->isRawExplainSupported('mariadb', []));
    }

    public function testRawExplainSupportedForPgsql(): void
    {
        static::assertTrue($this->explain->isRawExplainSupported('pgsql', []));
    }

    public function testRawExplainNotSupportedForSqlite(): void
    {
        static::assertFalse($this->explain->isRawExplainSupported('sqlite', []));
    }

    public function testRawExplainNotSupportedForSqlsrv(): void
    {
        static::assertFalse($this->explain->isRawExplainSupported('sqlsrv', []));
    }

    public function testRawExplainNotSupportedWhenBindingsNull(): void
    {
        static::assertFalse($this->explain->isRawExplainSupported('mysql', null));
    }

    public function testRawExplainSupportedWithEmptyBindings(): void
    {
        static::assertTrue($this->explain->isRawExplainSupported('mysql', []));
    }
}
