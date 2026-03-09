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
        $this->assertTrue($this->explain->isReadOnlyQuery('SELECT * FROM users'));
    }

    public function testSelectQueryWithLeadingWhitespaceIsReadOnly(): void
    {
        $this->assertTrue($this->explain->isReadOnlyQuery('  SELECT * FROM users'));
    }

    public function testSelectQueryCaseInsensitiveIsReadOnly(): void
    {
        $this->assertTrue($this->explain->isReadOnlyQuery('select * FROM users'));
    }

    public function testWithQueryIsReadOnly(): void
    {
        $this->assertTrue($this->explain->isReadOnlyQuery('WITH cte AS (SELECT 1) SELECT * FROM cte'));
    }

    public function testWithQueryCaseInsensitiveIsReadOnly(): void
    {
        $this->assertTrue($this->explain->isReadOnlyQuery('with cte AS (SELECT 1) SELECT * FROM cte'));
    }

    public function testInsertQueryIsNotReadOnly(): void
    {
        $this->assertFalse($this->explain->isReadOnlyQuery('INSERT INTO users (name) VALUES (?)'));
    }

    public function testUpdateQueryIsNotReadOnly(): void
    {
        $this->assertFalse($this->explain->isReadOnlyQuery('UPDATE users SET name = ? WHERE id = ?'));
    }

    public function testDeleteQueryIsNotReadOnly(): void
    {
        $this->assertFalse($this->explain->isReadOnlyQuery('DELETE FROM users WHERE id = ?'));
    }

    public function testDropQueryIsNotReadOnly(): void
    {
        $this->assertFalse($this->explain->isReadOnlyQuery('DROP TABLE users'));
    }

    public function testSelectAsSubstringIsNotReadOnly(): void
    {
        $this->assertFalse($this->explain->isReadOnlyQuery('SELECTFOO'));
    }

    public function testWithAsSubstringIsNotReadOnly(): void
    {
        $this->assertFalse($this->explain->isReadOnlyQuery('WITHFOO'));
    }

    public function testRawExplainSupportedForMysql(): void
    {
        $this->assertTrue($this->explain->isRawExplainSupported('mysql', []));
    }

    public function testRawExplainSupportedForMariadb(): void
    {
        $this->assertTrue($this->explain->isRawExplainSupported('mariadb', []));
    }

    public function testRawExplainSupportedForPgsql(): void
    {
        $this->assertTrue($this->explain->isRawExplainSupported('pgsql', []));
    }

    public function testRawExplainNotSupportedForSqlite(): void
    {
        $this->assertFalse($this->explain->isRawExplainSupported('sqlite', []));
    }

    public function testRawExplainNotSupportedForSqlsrv(): void
    {
        $this->assertFalse($this->explain->isRawExplainSupported('sqlsrv', []));
    }

    public function testRawExplainNotSupportedWhenBindingsNull(): void
    {
        $this->assertFalse($this->explain->isRawExplainSupported('mysql', null));
    }

    public function testRawExplainSupportedWithEmptyBindings(): void
    {
        $this->assertTrue($this->explain->isRawExplainSupported('mysql', []));
    }
}
