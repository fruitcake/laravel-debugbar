<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\CollectorProviders;

use Fruitcake\LaravelDebugbar\DataCollector\AiCollector;
use Illuminate\Contracts\Events\Dispatcher;
use Laravel\Ai\AiManager;
use Laravel\Ai\Events\AgentPrompted;
use Laravel\Ai\Events\AgentStreamed;
use Laravel\Ai\Events\ToolInvoked;

class AiCollectorProvider extends AbstractCollectorProvider
{
    /**
     * @param array<string, mixed> $options
     */
    public function __invoke(Dispatcher $events, array $options): void
    {
        if (! class_exists(AiManager::class)) {
            return;
        }

        $collector = new AiCollector($options['values'] ?? true);
        $this->addCollector($collector);

        $events->listen(ToolInvoked::class, function (ToolInvoked $event) use ($collector): void {
            if ($this->debugbar->isEnabled()) {
                $collector->bufferToolInvocation($event);
            }
        });

        foreach ([AgentPrompted::class, AgentStreamed::class] as $eventClass) {
            $events->listen($eventClass, function ($event) use ($collector): void {
                if ($this->debugbar->isEnabled()) {
                    $collector->recordAgentPrompted($event);
                }
            });
        }
    }
}
