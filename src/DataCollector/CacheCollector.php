<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\DataCollector;

use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\HasTimeDataCollector;
use DebugBar\DataCollector\Resettable;
use DebugBar\DataCollector\TimeDataCollector;
use Illuminate\Cache\Events\{CacheEvent,
    CacheFailedOver,
    CacheFlushed,
    CacheFlushFailed,
    CacheFlushing,
    CacheHit,
    CacheMissed,
    ForgettingKey,
    KeyForgetFailed,
    KeyForgotten,
    KeyWriteFailed,
    KeyWritten,
    RetrievingKey,
    WritingKey};
use Illuminate\Support\Facades\Route;
use Throwable;

class CacheCollector extends TimeDataCollector implements AssetProvider, Resettable
{
    use HasTimeDataCollector;

    protected bool $collectValues = false;

    protected array $eventStarts = [];

    protected array $classMap = [
        CacheHit::class => ['hit', RetrievingKey::class],
        CacheMissed::class => ['missed', RetrievingKey::class],
        CacheFlushed::class => ['flushed', CacheFlushing::class],
        CacheFlushFailed::class => ['flush_failed', CacheFlushing::class],
        KeyWritten::class => ['written', WritingKey::class],
        KeyWriteFailed::class => ['write_failed', WritingKey::class],
        KeyForgotten::class => ['forgotten', ForgettingKey::class],
        KeyForgetFailed::class => ['forget_failed', ForgettingKey::class],
    ];

    public function __construct(float $requestStartTime, bool $collectValues)
    {
        parent::__construct($requestStartTime);

        $this->collectValues = $collectValues;
        $this->memoryMeasure = true;
    }

    public function getCacheEvents(): array
    {
        return $this->classMap;
    }

    public function onCacheEvent(CacheEvent|CacheFailedOver|CacheFlushed|CacheFlushFailed|CacheFlushing $event): void
    {
        $class = get_class($event);
        $params = get_object_vars($event);
        $label = $this->classMap[$class][0];
        $startHashKey = $this->getEventHash($this->classMap[$class][1] ?? '', $params);

        if (isset($params['value'])) {
            if (!($params['value'] instanceof \Closure || is_resource($params['value']))) {
                try {
                    $params['memoryUsage'] = strlen(serialize($params['value'])) * 8;
                } catch (Throwable) {
                }
            }

            if (!$this->collectValues) {
                unset($params['value']);
            }
        }

        $time = microtime(true);
        $startTime = $this->eventStarts[$startHashKey] ?? $time;

        $this->addMeasure($label . "\t" . ($params['key'] ?? ''), $startTime, $time, $params);

        if ($this->hasTimeDataCollector()) {
            $this->addTimeMeasure('Cache ' . $label . "\t" . ($params['key'] ?? ''), $startTime, $time);
        }

        if (isset($event->key) && in_array($label, ['hit', 'written'], true) && Route::has('debugbar.cache.delete')) {
            $measureIndex = array_key_last($this->measures);
            $this->measures[$measureIndex]['delete_url'] = url()->signedRoute('debugbar.cache.delete', [
                'key' => urlencode((string) $event->key),
                'tags' => $params['tags'] ?? [],
            ]);
        }
    }

    public function onStartCacheEvent(mixed $event): void
    {
        $startHashKey = $this->getEventHash(get_class($event), get_object_vars($event));
        $this->eventStarts[$startHashKey] = microtime(true);
    }

    protected function getEventHash(string $class, array $params): string
    {
        unset($params['value']);

        return $class . ':' . substr(hash('sha256', json_encode($params)), 0, 12);
    }

    public function collect(): array
    {
        $data = parent::collect();
        $data['nb_measures'] = $data['count'] = count($data['measures']);
        // Replaces the generic timeline summary from TimeDataCollector.
        $data['summary'] = $this->summarizeCacheEvents($data['measures']);

        return $data;
    }

    /**
     * Cache traffic by action, plus a hit ratio when there were reads.
     *
     * Measure labels are "{action}\t{key}", so the action is the part before the tab.
     * Only key *names* are listed; values stay in the panel.
     *
     * @param array<int, array<string, mixed>> $measures
     *
     * @return array<string, mixed>
     */
    protected function summarizeCacheEvents(array $measures, int $maxKeys = 10): array
    {
        if (!$measures) {
            return [];
        }

        $counts = [];
        $missed = [];
        foreach ($measures as $measure) {
            [$action, $key] = array_pad(explode("\t", (string) ($measure['label'] ?? ''), 2), 2, '');
            $counts[$action] = ($counts[$action] ?? 0) + 1;

            if ($action === 'missed' && $key !== '') {
                $missed[] = $key;
            }
        }

        arsort($counts);
        $summary = ['events' => count($measures), 'by_action' => $counts];

        $reads = ($counts['hit'] ?? 0) + ($counts['missed'] ?? 0);
        if ($reads > 0) {
            $summary['hit_ratio'] = round(($counts['hit'] ?? 0) / $reads * 100) . '%';
        }

        if ($missed) {
            $missed = array_values(array_unique($missed));
            $extra = count($missed) - $maxKeys;
            $summary['missed_keys'] = $extra > 0
                ? array_merge(array_slice($missed, 0, $maxKeys), ["(+{$extra} more)"])
                : $missed;
        }

        return $summary;
    }

    public function reset(): void
    {
        parent::reset();
        $this->eventStarts = [];
    }

    public function getName(): string
    {
        return 'cache';
    }

    public function getWidgets(): array
    {
        return [
            'cache' => [
                'icon' => 'clipboard-text',
                'widget' => 'PhpDebugBar.Widgets.LaravelCacheWidget',
                'map' => 'cache',
                'default' => '{}',
            ],
            'cache:summary' => [
                'map' => 'cache.summary',
            ],
            'cache:badge' => [
                'map' => 'cache.nb_measures',
                'default' => 'null',
            ],
        ];
    }

    public function getAssets(): array
    {
        return [
            'js' => __DIR__ . '/../../resources/cache/widget.js',
        ];
    }
}
