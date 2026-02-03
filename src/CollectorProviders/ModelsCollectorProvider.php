<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\CollectorProviders;

use DebugBar\DataCollector\ObjectCountCollector;
use Illuminate\Contracts\Events\Dispatcher;

class ModelsCollectorProvider extends AbstractCollectorProvider
{
    public function __invoke(Dispatcher $events, array $options): void
    {
        $modelsCollector = new ObjectCountCollector('models');
        $this->addCollector($modelsCollector);

        $eventList = ['retrieved', 'created', 'updated', 'deleted'];
        $modelsCollector->setKeyMap(array_combine($eventList, array_map('ucfirst', $eventList)));
        $modelsCollector->collectCountSummary(true);
        foreach ($eventList as $event) {
            $events->listen("eloquent.{$event}: *", function ($event, $models) use ($modelsCollector): void {
                $event = explode(': ', $event);
                $count = count(array_filter($models));
                $modelsCollector->countClass($event[1], $count, explode('.', $event[0])[1]);
            });
        }
    }
}
