<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar;

use DebugBar\DataFormatter\DataFormatter;
use DebugBar\DataFormatter\DataFormatterInterface;
use Fruitcake\LaravelDebugbar\Console\ClearCommand;
use Fruitcake\LaravelDebugbar\Support\Octane\ResetDebugbar;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Events\Terminating;
use Illuminate\Foundation\Http\Events\RequestHandled;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Collection;
use Laravel\Octane\Events\RequestReceived;

class ServiceProvider extends \Illuminate\Support\ServiceProvider
{
    /**
     * Register the service provider.
     *
     */
    public function register(): void
    {
        $configPath = __DIR__ . '/../config/debugbar.php';
        $this->mergeConfigFrom($configPath, 'debugbar');

        $this->app->alias(
            DataFormatter::class,
            DataFormatterInterface::class,
        );

        $this->app->singleton(LaravelDebugbar::class);
        $this->app->alias(LaravelDebugbar::class, 'debugbar');

        Collection::macro('debug', function (): \Illuminate\Support\Collection {
            debug($this);
            return $this;
        });
    }

    /**
     * Bootstrap the application events.
     *
     */
    public function boot(Dispatcher $events): void
    {
        if ($this->app->runningInConsole()) {
            $configPath = __DIR__ . '/../config/debugbar.php';
            $this->publishes([$configPath => $this->getConfigPath()], 'config');

            $this->commands([ClearCommand::class]);
        }

        $this->loadRoutesFrom(__DIR__ . '/debugbar-routes.php');

        // Resolve the LaravelDebugbar instance during boot to force it to be loaded in the Octane sandbox
        try {
            $debugbar = $this->app->make(LaravelDebugbar::class);
        } catch (\Throwable $e) {
            // Errors can occur when removing LaravelDebugbar with composer scripts, when php-debugbar is not installed
            report($e);
            return;
        }

        // Reset the debugbar instance on each new Octane request
        $events->listen(RequestReceived::class, ResetDebugbar::class);

        // Handle response
        $events->listen(RequestHandled::class, function ($event) use ($debugbar): void {
            $debugbar->handleResponse($event->request, $event->response);
        });

        // Store any data collected during termination but not already stored
        $events->listen(Terminating::class, function ($event) use ($debugbar): void {
            $debugbar->terminate();
        });

        if (config('debugbar.collect_jobs')) {
            $events->listen(JobProcessing::class, function (JobProcessing $event) use ($debugbar): void {
                // Sync jobs in non-console jobs are just requests
                if ($event->connectionName === 'sync' && !$this->app->runningInConsole()) {
                    return;
                }

                $debugbar->enable();
                $debugbar->setProcessingJob($event->job);
            });

            $events->listen(JobProcessed::class, function (JobProcessed $event) use ($debugbar): void {
                if ($debugbar->getProcessingJob()) {
                    $debugbar->collect();
                    $debugbar->setProcessingJob(null);
                    $debugbar->reset();
                }
            });
        }

        // Exclude debugbar cookies from encryption
        EncryptCookies::except($debugbar->getStackDataSessionNamespace());

        // Attach listeners when debugbar should be enabled
        if ($debugbar->isEnabled() && !$debugbar->requestIsExcluded($this->app['request'])) {
            $debugbar->boot();
        }

        // Register boot time, regardless of already being booted
        $this->booted(fn() => $debugbar->booted());
    }

    /**
     * Get the config path
     *
     */
    protected function getConfigPath(): string
    {
        return config_path('debugbar.php');
    }
}
