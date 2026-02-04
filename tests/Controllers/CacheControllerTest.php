<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Controllers;

use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\DataProvider;
use ReflectionObject;

class CacheControllerTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app->resolving(LaravelDebugbar::class, function ($debugbar): void {
            (new ReflectionObject($debugbar))
                ->getProperty('enabled')
                ->setValue($debugbar, true);
        });
    }

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

        $this->delete('/_debugbar/cache/' . $key)->assertUnauthorized();

        static::assertTrue(Cache::has($key));
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
