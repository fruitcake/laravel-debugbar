<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\CollectorProviders;

use Fruitcake\LaravelDebugbar\DataCollector\HttpClientCollector;
use DebugBar\DataCollector\TimeDataCollector;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Events\ConnectionFailed;
use Illuminate\Http\Client\Events\ResponseReceived;

class HttpClientCollectorProvider extends AbstractCollectorProvider
{
    protected ?HttpClientCollector $httpClientCollector = null;

    public function __invoke(Dispatcher $events, array $options): void
    {
        $httpClientCollector = new HttpClientCollector('http_client');
        if ($this->hasCollector('time') && ($options['timeline'] ?? true)) {
            /** @var TimeDataCollector   $timeCollector */
            $timeCollector = $this->getCollector('time');
            $httpClientCollector->setTimeDataCollector($timeCollector);
        }

        $this->httpClientCollector = $httpClientCollector;

        $masked = $options['masked'] ?? [];
        $httpClientCollector->addMaskedKeys($masked);

        $this->addCollector($httpClientCollector);

        $events->listen(ResponseReceived::class, fn(ResponseReceived $e) => $this->addEvent($e));
        $events->listen(ConnectionFailed::class, fn(ConnectionFailed $e) => $this->addEvent($e));
    }

    protected function addEvent(ResponseReceived|ConnectionFailed $event): void
    {
        try {
            $this->httpClientCollector->addEvent($event);
        } catch (\Throwable $e) {
            $this->addThrowable($e);
        }
    }
}
