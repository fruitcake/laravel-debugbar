<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use Fruitcake\LaravelDebugbar\DataCollector\CacheCollector;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\KeyWritten;
use PHPUnit\Framework\Attributes\DataProvider;

class CacheCollectorTest extends TestCase
{
    public function testItCollectsCacheEvents(): void
    {
        debugbar()->boot();

        /** @var CacheCollector $collector */
        $collector = debugbar()->getCollector('cache');

        $collector->onCacheEvent(new KeyWritten('array', 'test-key', 'test-value', 60, []));
        $collector->onCacheEvent(new CacheHit('array', 'test-key', 'test-value', []));

        $data = $collector->collect();

        static::assertEquals(2, $data['nb_measures']);
    }

    #[DataProvider('cacheKeyProvider')]
    public function testItGeneratesDeleteUrlForCacheHit(string $key): void
    {
        debugbar()->boot();

        /** @var CacheCollector $collector */
        $collector = debugbar()->getCollector('cache');

        $collector->onCacheEvent(new CacheHit('array', $key, 'value', []));

        $data        = $collector->collect();
        $lastMeasure = end($data['measures']);

        static::assertArrayHasKey('delete_url', $lastMeasure);
        static::assertStringContainsString('_debugbar/cache/', $lastMeasure['delete_url']);
        static::assertStringContainsString(urlencode($key), $lastMeasure['delete_url']);
    }

    #[DataProvider('cacheKeyProvider')]
    public function testItGeneratesDeleteUrlForKeyWritten(string $key): void
    {
        debugbar()->boot();

        /** @var CacheCollector $collector */
        $collector = debugbar()->getCollector('cache');

        $collector->onCacheEvent(new KeyWritten('array', $key, 'value', 60, []));

        $data        = $collector->collect();
        $lastMeasure = end($data['measures']);

        static::assertArrayHasKey('delete_url', $lastMeasure);
        static::assertStringContainsString(urlencode($key), $lastMeasure['delete_url']);
    }

    /** @return array<string, list<string>> */
    public static function cacheKeyProvider(): array
    {
        return [
            'simple key'                      => ['simple-key'],
            'key with route parameter syntax' => ['pattern::category,resources/{resource}'],
            'key with colons and slashes'     => ['key:with:colons/and/slashes'],
        ];
    }
}
