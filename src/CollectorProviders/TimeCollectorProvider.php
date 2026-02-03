<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\CollectorProviders;

use DebugBar\DataCollector\TimeDataCollector;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Routing\Events\PreparingResponse;
use Illuminate\Routing\Events\ResponsePrepared;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Routing\Events\Routing;

class TimeCollectorProvider extends AbstractCollectorProvider
{
    public function __invoke(Application $app, Request $request, Dispatcher $events, array $options): void
    {
        if ($this->hasCollector('time')) {
            /** @var TimeDataCollector $timeCollector */
            $timeCollector = $this['time'];
        } else {
            $timeCollector = $this->debugbar->getTimeCollector();
            $this->addCollector($timeCollector);
        }

        if ($options['memory_usage'] ?? false) {
            $timeCollector->showMemoryUsage();
        }

        $events->listen(Routing::class, fn() => $timeCollector->startMeasure('Routing'));
        $events->listen(RouteMatched::class, fn() => $timeCollector->stopMeasure('Routing'));

        $events->listen(PreparingResponse::class, fn() => $timeCollector->startMeasure('Preparing Response'));
        $events->listen(ResponsePrepared::class, fn() => $timeCollector->stopMeasure('Preparing Response'));
    }
}
