<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\CollectorProviders;

use Fruitcake\LaravelDebugbar\DataCollector\GateCollector;
use Illuminate\Auth\Access\Events\GateEvaluated;
use Illuminate\Contracts\Events\Dispatcher;

class GateCollectorProvider extends AbstractCollectorProvider
{
    public function __invoke(Dispatcher $events, array $options): void
    {
        $gateCollector = new GateCollector('gate');
        $this->addCollector($gateCollector);

        if ($options['trace'] ?? false) {
            $gateCollector->collectFileTrace(true);
            $gateCollector->addBacktraceExcludePaths($options['exclude_paths'] ?? []);
        }

        if ($options['timeline'] ?? false) {
            $gateCollector->setTimeDataCollector($this->debugbar->getTimeCollector());
        }

        $events->listen(GateEvaluated::class, fn(GateEvaluated $event) => $gateCollector->addCheck($event->user, $event->ability, $event->result, $event->arguments));
    }
}
