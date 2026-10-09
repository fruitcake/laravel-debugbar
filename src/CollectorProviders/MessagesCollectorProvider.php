<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\CollectorProviders;

class MessagesCollectorProvider extends AbstractCollectorProvider
{
    public function __invoke(array $options): void
    {
        $messageCollector = $this->debugbar->getMessagesCollector();
        $this->addCollector($messageCollector);

        if ($options['trace'] ?? true) {
            $messageCollector->collectFileTrace(true);

            $excludePaths = $options['backtrace_exclude_paths'] ?? [];
            if ($excludePaths) {
                $messageCollector->addBacktraceExcludePaths($excludePaths);
            }
        }

        if ($options['timeline'] ?? true) {
            $messageCollector->setTimeDataCollector($this->debugbar->getTimeCollector());
        }

        if ($options['capture_dumps'] ?? false) {
            $originalHandler = \Symfony\Component\VarDumper\VarDumper::setHandler(function ($var, $label = null) use (&$originalHandler, $messageCollector): void {
                if ($originalHandler) {
                    $originalHandler($var, $label);
                }

                $messageCollector->addMessage($var, 'info', [(string)$label]);
            });
        }
    }
}
