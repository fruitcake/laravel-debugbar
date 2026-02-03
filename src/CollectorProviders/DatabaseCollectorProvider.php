<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\CollectorProviders;

use Fruitcake\LaravelDebugbar\DataCollector\QueryCollector;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Events\ConnectionEstablished;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Events\TransactionBeginning;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Database\Events\TransactionRolledBack;
use Illuminate\Routing\Router;

class DatabaseCollectorProvider extends AbstractCollectorProvider
{
    public function __invoke(Dispatcher $events, Router $router, array $options): void
    {
        $queryCollector = new QueryCollector();
        if ($options['timeline'] ?? false) {
            $timeCollector = $this->debugbar->getTimeCollector();
            $queryCollector->setTimeDataCollector($timeCollector);
        }

        $queryCollector->setLimits($options['soft_limit'] ?? 100, $options['hard_limit'] ?? 500);
        $queryCollector->setDurationBackground($options['duration_background'] ?? true);

        $threshold = $options['slow_threshold'] ?? false;
        if ($threshold && !($options['only_slow_queries'] ?? true)) {
            $queryCollector->setSlowThreshold($threshold);
        }

        if ($options['with_params'] ?? true) {
            $queryCollector->setRenderSqlWithParams(true);
        }

        if ($backtrace = ($options['backtrace'] ?? true)) {
            $queryCollector->setFindSource($backtrace, $router->getMiddleware());
        }

        if ($excludePaths = ($options['exclude_paths'] ?? [])) {
            $queryCollector->mergeExcludePaths($excludePaths);
        }

        if ($excludeBacktracePaths = ($options['backtrace_exclude_paths'] ?? [])) {
            $queryCollector->mergeBacktraceExcludePaths($excludeBacktracePaths);
        }

        if ($options['explain']['enabled'] ?? false) {
            $queryCollector->setExplainSource(true);
        }

        $this->addCollector($queryCollector);

        try {
            $events->listen(
                function (QueryExecuted $query) use ($queryCollector, $options): void {
                    // In case Debugbar is disabled after the listener was attached
                    if (!$this->debugbar->shouldCollect('db', true) || !$this->debugbar->isEnabled()) {
                        return;
                    }

                    $threshold = $options['slow_threshold'] ?? false;
                    $onlyThreshold = $options['only_slow_queries'] ?? true;

                    //allow collecting only queries slower than a specified amount of milliseconds
                    if (!$onlyThreshold || !$threshold || $query->time > $threshold) {
                        $queryCollector->addQuery($query);
                    }
                },
            );
        } catch (\Throwable $e) {
            $this->addCollectorException('Cannot listen to Queries', $e);
        }

        try {
            $events->listen(
                TransactionBeginning::class,
                fn($transaction) => $queryCollector->collectTransactionEvent('Begin Transaction', $transaction->connection),
            );

            $events->listen(
                TransactionCommitted::class,
                fn($transaction) => $queryCollector->collectTransactionEvent('Commit Transaction', $transaction->connection),
            );

            $events->listen(
                TransactionRolledBack::class,
                fn($transaction) => $queryCollector->collectTransactionEvent('Rollback Transaction', $transaction->connection),
            );

            $events->listen(
                'connection.*.beganTransaction',
                fn($event, $params) => $queryCollector->collectTransactionEvent('Begin Transaction', $params[0]),
            );

            $events->listen(
                'connection.*.committed',
                fn($event, $params) =>  $queryCollector->collectTransactionEvent('Commit Transaction', $params[0]),
            );

            $events->listen(
                'connection.*.rollingBack',
                fn($event, $params) => $queryCollector->collectTransactionEvent('Rollback Transaction', $params[0]),
            );

            $events->listen(
                function (ConnectionEstablished $event) use ($queryCollector, $options): void {
                    $queryCollector->collectTransactionEvent('Connection Established', $event->connection);

                    if ($options['memory_usage'] ?? false) {
                        $event->connection->beforeExecuting(function () use ($queryCollector): void {
                            $queryCollector->startMemoryUsage();
                        });
                    }
                },
            );
        } catch (\Throwable $e) {
            $this->addCollectorException('Cannot listen to Queries', $e);
        }
    }
}
