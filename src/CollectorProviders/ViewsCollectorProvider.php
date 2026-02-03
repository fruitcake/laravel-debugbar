<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\CollectorProviders;

use Fruitcake\LaravelDebugbar\DataCollector\ViewCollector;
use Illuminate\Contracts\Events\Dispatcher;

class ViewsCollectorProvider extends AbstractCollectorProvider
{
    public function __invoke(Dispatcher $events, array $options): void
    {
        $collectData = $options['data'] ?? false;
        $excludePaths = $options['exclude_paths'] ?? [];
        $group = $options['group'] ?? true;

        $viewCollector = new ViewCollector($collectData, $excludePaths, $group);

        if ($options['timeline'] ?? true) {
            $timeCollector = $this->debugbar->getTimeCollector();
            $viewCollector->setTimeDataCollector($timeCollector);
        }

        $this->addCollector($viewCollector);
        $events->listen(
            'composing:*',
            function ($event, $params) use ($viewCollector): void {
                $viewCollector->addView($params[0]);
            },
        );
    }
}
