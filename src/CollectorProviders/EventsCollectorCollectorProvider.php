<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\CollectorProviders;

use Fruitcake\LaravelDebugbar\DataCollector\EventCollector;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;

class EventsCollectorCollectorProvider extends AbstractCollectorProvider
{
    public function __invoke(Request $request, Dispatcher $events, array $options): void
    {
        $startTime = $request->server('REQUEST_TIME_FLOAT');

        $collectData = $options['data'] ?? false;
        $collectListeners = $options['listeners'] ?? false;
        $excludedEvents = $options['excluded'] ?? [];

        $eventCollector = new EventCollector($startTime ? (float) $startTime : null);
        if ($collectData) {
            $eventCollector->setCollectValues($collectData);
        }
        if ($collectListeners) {
            $eventCollector->setCollectListeners($collectListeners);
        }
        if ($excludedEvents) {
            $eventCollector->setExcludedEvents($excludedEvents);
        }

        $this->addCollector($eventCollector);

        $events->listen('*', function ($event, $data = []) use ($eventCollector): void {
            if ($this->debugbar->isEnabled()) {
                $eventCollector->onWildcardEvent($event, $data);
            }
        });
    }
}
