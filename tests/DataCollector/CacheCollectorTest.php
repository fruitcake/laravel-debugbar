<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use Fruitcake\LaravelDebugbar\DataCollector\CacheCollector;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Cache\Events\CacheHit;
use Illuminate\Cache\Events\CacheMissed;
use Illuminate\Cache\Events\KeyForgotten;
use Illuminate\Cache\Events\KeyWritten;
use Illuminate\Cache\Events\RetrievingKey;
use Illuminate\Cache\Events\WritingKey;
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

    #[DataProvider('sizeDataProvider')]
    public function testItCalculatesMemoryUsageForKeyWritten(mixed $value, int $expectedSize): void
    {
        debugbar()->boot();

        /** @var CacheCollector $collector */
        $collector = debugbar()->getCollector('cache');

        $collector->onCacheEvent(new KeyWritten('array', 'size-key', $value, 60, []));

        $data = $collector->collect();
        $lastMeasure = end($data['measures']);

        static::assertEquals($expectedSize, $lastMeasure['memory']);
    }

    #[DataProvider('sizeDataProvider')]
    public function testItCalculatesMemoryUsageForCacheHit(mixed $value, int $expectedSize): void
    {
        debugbar()->boot();

        /** @var CacheCollector $collector */
        $collector = debugbar()->getCollector('cache');

        $collector->onCacheEvent(new CacheHit('array', 'size-key', $value, []));

        $data = $collector->collect();
        $lastMeasure = end($data['measures']);

        static::assertEquals($expectedSize, $lastMeasure['memory']);
    }

    public function testItHandlesClosureValueGracefully(): void
    {
        debugbar()->boot();

        /** @var CacheCollector $collector */
        $collector = debugbar()->getCollector('cache');

        $collector->onCacheEvent(new KeyWritten('array', 'closure-key', function () {
            return 'hello';
        }, 60, []));

        $data = $collector->collect();
        $lastMeasure = end($data['measures']);

        // Closures can't be serialized, so memory should be 0
        static::assertEquals(0, $lastMeasure['memory']);
        static::assertEquals(1, $data['nb_measures']);
    }

    public function testItHandlesClosureValueInCacheHitGracefully(): void
    {
        debugbar()->boot();

        /** @var CacheCollector $collector */
        $collector = debugbar()->getCollector('cache');

        $collector->onCacheEvent(new CacheHit('array', 'closure-key', function () {
            return 'hello';
        }, []));

        $data = $collector->collect();
        $lastMeasure = end($data['measures']);

        // Closures can't be serialized, so memory should be 0
        static::assertEquals(0, $lastMeasure['memory']);
        static::assertEquals(1, $data['nb_measures']);
    }

    public function testCacheMissHasNoMemoryUsage(): void
    {
        debugbar()->boot();

        /** @var CacheCollector $collector */
        $collector = debugbar()->getCollector('cache');

        $collector->onCacheEvent(new CacheMissed('array', 'miss-key', []));

        $data = $collector->collect();
        $lastMeasure = end($data['measures']);

        // CacheMissed has no value, so memory should be 0
        static::assertEquals(0, $lastMeasure['memory']);
    }

    public function testItCollectsRememberMissPattern(): void
    {
        debugbar()->boot();

        /** @var CacheCollector $collector */
        $collector = debugbar()->getCollector('cache');

        // Simulate a remember() call: first a RetrievingKey, then a miss, then a write
        $collector->onStartCacheEvent(new RetrievingKey('array', 'remember-key', []));
        $collector->onCacheEvent(new CacheMissed('array', 'remember-key', []));

        $collector->onStartCacheEvent(new WritingKey('array', 'remember-key', 'computed-value', 300, []));
        $collector->onCacheEvent(new KeyWritten('array', 'remember-key', 'computed-value', 300, []));

        $data = $collector->collect();

        static::assertEquals(2, $data['nb_measures']);

        $measures = array_values($data['measures']);
        static::assertStringContainsString('missed', $measures[0]['label']);
        static::assertStringContainsString('written', $measures[1]['label']);
        static::assertGreaterThan(0, $measures[1]['memory']);
    }

    public function testItCollectsRememberHitPattern(): void
    {
        debugbar()->boot();

        /** @var CacheCollector $collector */
        $collector = debugbar()->getCollector('cache');

        // Simulate a remember() call that hits cache
        $collector->onStartCacheEvent(new RetrievingKey('array', 'remember-key', []));
        $collector->onCacheEvent(new CacheHit('array', 'remember-key', 'cached-value', []));

        $data = $collector->collect();

        static::assertEquals(1, $data['nb_measures']);

        $lastMeasure = end($data['measures']);
        static::assertStringContainsString('hit', $lastMeasure['label']);
        static::assertGreaterThan(0, $lastMeasure['memory']);
    }

    public function testItCollectsForgottenEvent(): void
    {
        debugbar()->boot();

        /** @var CacheCollector $collector */
        $collector = debugbar()->getCollector('cache');

        $collector->onCacheEvent(new KeyForgotten('array', 'forget-key', []));

        $data = $collector->collect();

        static::assertEquals(1, $data['nb_measures']);

        $lastMeasure = end($data['measures']);
        static::assertStringContainsString('forgotten', $lastMeasure['label']);
    }

    public function testStartEventTimingIsUsed(): void
    {
        debugbar()->boot();

        /** @var CacheCollector $collector */
        $collector = debugbar()->getCollector('cache');

        // Use CacheMissed (no value property) so the event hash matches between start and end
        $collector->onStartCacheEvent(new RetrievingKey('array', 'timed-key', []));

        usleep(10000);

        $collector->onCacheEvent(new CacheMissed('array', 'timed-key', []));

        $data = $collector->collect();
        $lastMeasure = end($data['measures']);

        static::assertGreaterThan(0.001, $lastMeasure['duration']);
    }

    /** @return array<string, array{mixed, int}> */
    public static function sizeDataProvider(): array
    {
        return [
            'string value' => ['hello world', strlen(serialize('hello world')) * 8],
            'integer value' => [42, strlen(serialize(42)) * 8],
            'float value' => [3.14, strlen(serialize(3.14)) * 8],
            'boolean value' => [true, strlen(serialize(true)) * 8],
            'null value' => [null, 0], // null fails isset() check, so no memoryUsage is calculated
            'array value' => [['a', 'b', 'c'], strlen(serialize(['a', 'b', 'c'])) * 8],
            'nested array' => [['key' => ['nested' => 'value']], strlen(serialize(['key' => ['nested' => 'value']])) * 8],
            'empty string' => ['', strlen(serialize('')) * 8],
            'large string' => [str_repeat('x', 1000), strlen(serialize(str_repeat('x', 1000))) * 8],
            'stdClass object' => [(object) ['foo' => 'bar'], strlen(serialize((object) ['foo' => 'bar'])) * 8],
        ];
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
