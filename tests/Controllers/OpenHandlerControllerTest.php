<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Controllers;

use Fruitcake\LaravelDebugbar\Tests\DebugbarTest;

class OpenHandlerControllerTest extends DebugbarTest
{
    public function testOpenHandlerReturnsStorageClosedMessageWhenStorageIsNotOpen(): void
    {
        $this->app['config']->set('debugbar.storage.open', false);
        $this->resetStorageOpen();

        $response = $this->get('/_debugbar/open?op=find');

        $response->assertOk();
        $response->assertJsonFragment(['method' => 'ERROR']);
    }

    public function testOpenHandlerAllowsGetOpWithoutStorageOpen(): void
    {
        $this->app['config']->set('debugbar.storage.open', false);
        $this->resetStorageOpen();

        // op=get is always allowed even without storage open
        $response = $this->get('/_debugbar/open');

        $response->assertOk();
    }

    public function testOpenHandlerWorksWhenStorageIsOpen(): void
    {
        $this->app['config']->set('debugbar.storage.open', true);
        $this->resetStorageOpen();
        $this->ensureStorageDirectory();

        $response = $this->get('/_debugbar/open?op=find');

        $response->assertOk();
    }

    public function testOpenHandlerStorageOpenCallbackReceivesRequest(): void
    {
        $receivedRequest = null;

        $this->app['config']->set('debugbar.storage.open', function ($request) use (&$receivedRequest) {
            $receivedRequest = $request;

            return $request->header('X-Debugbar-Token') === 'valid-token';
        });
        $this->resetStorageOpen();

        // Without the header, callback returns false — storage is closed
        $response = $this->get('/_debugbar/open?op=find');
        $response->assertOk();
        $response->assertJsonFragment(['method' => 'ERROR']);
        static::assertNotNull($receivedRequest);

        // With the header, callback returns true — storage is open
        $this->resetStorageOpen();
        $this->ensureStorageDirectory();
        $response = $this->get('/_debugbar/open?op=find', ['X-Debugbar-Token' => 'valid-token']);
        $response->assertOk();

        $data = $response->json();
        if (is_array($data) && isset($data[0]['method'])) {
            static::assertNotEquals('ERROR', $data[0]['method']);
        }
    }

    private function ensureStorageDirectory(): void
    {
        $path = config('debugbar.storage.path', storage_path('debugbar'));
        if (!is_dir($path)) {
            mkdir($path, 0o755, true);
        }
    }
}
