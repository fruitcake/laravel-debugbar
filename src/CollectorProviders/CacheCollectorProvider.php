<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\CollectorProviders;

use Fruitcake\LaravelDebugbar\DataCollector\CacheCollector;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;

class CacheCollectorProvider extends AbstractCollectorProvider
{
    public function __invoke(Request $request, Dispatcher $events, array $options): void
    {
        $collectValues = $options['values'] ?? false;
        $startTime = (float) $request->server('REQUEST_TIME_FLOAT');
        $cacheCollector = new CacheCollector($startTime, $collectValues);
        $this->addCollector($cacheCollector);

        if ($options['timeline'] ?? false) {
            $cacheCollector->setTimeDataCollector($this->debugbar->getTimeCollector());
        }

        $classMap = $cacheCollector->getCacheEvents();
        foreach (array_keys($classMap) as $eventClass) {
            $events->listen($eventClass, function ($event) use ($cacheCollector): void {
                if ($this->debugbar->isEnabled()) {
                    $cacheCollector->onCacheEvent($event);
                }
            });
        }

        $startEvents = array_unique(array_filter(array_map(
            fn($values) => $values[1] ?? null,
            array_values($classMap),
        )));

        foreach ($startEvents as $eventClass) {
            $events->listen($eventClass, function ($event) use ($cacheCollector): void {
                if ($this->debugbar->isEnabled()) {
                    $cacheCollector->onStartCacheEvent($event);
                }
            });
        }
    }
}
