<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Controllers;

use Fruitcake\LaravelDebugbar\Tests\DebugbarTest;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;

class CacheControllerTest extends DebugbarTest
{
    #[DataProvider('cacheKeyProvider')]
    public function testItDeletesCacheKeyWithSignedUrl(string $key): void
    {
        Cache::put($key, 'test-value');
        static::assertTrue(Cache::has($key));

        $url = url()->signedRoute('debugbar.cache.delete', ['key' => urlencode($key)]);

        $this->delete($url)->assertOk()->assertJson(['success' => true]);

        static::assertFalse(Cache::has($key));
    }

    public function testItRejectsRequestWithInvalidSignature(): void
    {
        $key = 'test-key';
        Cache::put($key, 'test-value');

        $this->delete('/_debugbar/cache/' . $key)->assertForbidden();

        static::assertTrue(Cache::has($key));
    }

    public function testItRejectsRequestWhenStorageIsNotOpen(): void
    {
        $this->app['config']->set('debugbar.storage.open', false);
        $this->resetStorageOpen();

        $key = 'test-key';
        Cache::put($key, 'test-value');

        $url = url()->signedRoute('debugbar.cache.delete', ['key' => urlencode($key)]);

        $this->delete($url)->assertForbidden();

        static::assertTrue(Cache::has($key));
    }

    public function testItRejectsInvalidTagsParameter(): void
    {
        $key = 'test-key';
        Cache::put($key, 'test-value');

        $url = url()->signedRoute('debugbar.cache.delete', ['key' => urlencode($key), 'tags' => 'not-an-array']);

        $this->deleteJson($url)->assertUnprocessable();
    }

    /** @return array<string, list<string>> */
    public static function cacheKeyProvider(): array
    {
        return [
            'simple key'                      => ['test-delete-key'],
            'key with route parameter syntax' => ['pattern::category,resources/{resource}'],
            'key with colons and slashes'     => ['key:with:colons/and/slashes'],
        ];
    }
}
