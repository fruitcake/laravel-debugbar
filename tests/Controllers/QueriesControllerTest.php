<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Controllers;

use Fruitcake\LaravelDebugbar\Tests\DebugbarTest;

class QueriesControllerTest extends DebugbarTest
{
    public function testExplainReturnsErrorWhenStorageIsNotOpen(): void
    {
        $this->app['config']->set('debugbar.storage.open', false);
        $this->app['config']->set('debugbar.options.db.explain', true);
        $this->resetStorageOpen();

        $response = $this->postJson('/_debugbar/queries/explain', [
            'id' => 'uuid123',
            'hash' => 'abc123',
        ]);

        $response->assertStatus(400);
        $response->assertJson(['success' => false]);
    }

    public function testExplainReturnsErrorWhenExplainIsDisabled(): void
    {
        $this->app['config']->set('debugbar.storage.open', true);
        $this->app['config']->set('debugbar.options.db.explain', false);

        $response = $this->postJson('/_debugbar/queries/explain', [
            'id' => 'uuid123',
            'hash' => 'abc123',
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'success' => false,
            'message' => 'EXPLAIN is currently disabled in the Debugbar.',
        ]);
    }

    public function testExplainValidatesRequiredFields(): void
    {
        $this->app['config']->set('debugbar.storage.open', true);
        $this->app['config']->set('debugbar.options.db.explain', true);

        $response = $this->postJson('/_debugbar/queries/explain', []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['id', 'hash']);
    }

    public function testExplainValidatesModeValues(): void
    {
        $this->app['config']->set('debugbar.storage.open', true);
        $this->app['config']->set('debugbar.options.db.explain', true);

        $response = $this->postJson('/_debugbar/queries/explain', [
            'id' => 'uuid123',
            'hash' => 'abc123',
            'mode' => 'invalid',
        ]);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors(['mode']);
    }

    public function testExplainAcceptsValidModeValues(): void
    {
        $this->app['config']->set('debugbar.storage.open', true);
        $this->app['config']->set('debugbar.options.db.explain', true);

        foreach (['visual', 'result'] as $mode) {
            $response = $this->postJson('/_debugbar/queries/explain', [
                'id' => 'uuid123',
                'hash' => 'abc123',
                'mode' => $mode,
            ]);

            static::assertNotEquals(422, $response->getStatusCode(), "Mode '{$mode}' should be accepted");
        }
    }
}
