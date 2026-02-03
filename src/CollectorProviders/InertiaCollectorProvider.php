<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\CollectorProviders;

use Fruitcake\LaravelDebugbar\DataCollector\InertiaCollector;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Routing\Events\ResponsePrepared;

class InertiaCollectorProvider extends AbstractCollectorProvider
{
    public function __invoke(Application $app, Dispatcher $events, array $options): void
    {
        if ($app->bound('inertia.view-finder')) {

            $inertiaCollector = new InertiaCollector(true, [], false);

            $this->addCollector($inertiaCollector);

            $events->listen(ResponsePrepared::class, fn(ResponsePrepared $e) => $inertiaCollector->addFromResponse($e->response));
            $events->listen('composing:*', fn($event, $params) => $inertiaCollector->addFromView($params[0]));
        }
    }

}
