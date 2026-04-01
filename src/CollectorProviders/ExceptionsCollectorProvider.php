<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\CollectorProviders;

use Illuminate\Contracts\Debug\ExceptionHandler;

class ExceptionsCollectorProvider extends AbstractCollectorProvider
{
    public function __invoke(ExceptionHandler $exceptionHandler, array $options): void
    {
        $exceptionCollector = $this->debugbar->getExceptionsCollector();
        $this->addCollector($exceptionCollector);
        $exceptionCollector->setChainExceptions($options['chain'] ?? true);

        try {
            if (method_exists($exceptionHandler, 'reportable')) {
                $exceptionHandler->reportable(function (\Throwable $exception) use ($exceptionCollector): void {
                    $exceptionCollector->addThrowable($exception);
                });
            }
        } catch (\Throwable $e) {
            $this->addCollectorException('Cannot listen to Exceptions in ExceptionHandler', $e);
        }
    }
}
