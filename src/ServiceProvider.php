<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar;

use DebugBar\DataFormatter\DataFormatter;
use DebugBar\DataFormatter\DataFormatterInterface;
use DebugBar\DebugBar;
use Fruitcake\LaravelDebugbar\Console\ClearCommand;
use Fruitcake\LaravelDebugbar\Console\FindCommand;
use Fruitcake\LaravelDebugbar\Console\GetCommand;
use Fruitcake\LaravelDebugbar\Console\QueriesCommand;
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
        $this->app->alias(LaravelDebugbar::class, DebugBar::class);

        Collection::macro('debug', function (): \Illuminate\Support\Collection {
            debug($this);
            return $this;
        });

        if (
            !$this->app['config']->get('debugbar.collectors.time', false)
            || !$this->app['config']->get('debugbar.collectors.views', false)
            || !$this->app['config']->get('debugbar.options.views.timeline', false)
            || !$this->app['config']->get('debugbar.options.views.timeline_duration', false)
        ) {
            return;
        }

        $this->app->extend(
            'view',
            function (Factory $factory, Container $application): Factory {
                assert($factory instanceof ViewFactory);
                $laravelDebugbar = $application->make(LaravelDebugbar::class);

                if (! $laravelDebugbar->isEnabled()) {
                    return $factory; // Do not swap the engine to save performance
                }

                $extensions = array_reverse($factory->getExtensions());
                $engines = array_flip($extensions);
                $enginesResolver = $application->make('view.engine.resolver');

                foreach ($engines as $engine => $extension) {
                    $resolved = $enginesResolver->resolve($engine);

                    $factory->addExtension($extension, $engine, function () use ($resolved, $laravelDebugbar): Engine {
                        return new DebugbarViewEngine($resolved, $laravelDebugbar);
                    });
                }

                // returns original order of extensions
                foreach ($extensions as $extension => $engine) {
                    $factory->addExtension($extension, $engine);
                }

                return $factory;
            }
        );
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

            $this->commands([FindCommand::class, GetCommand::class, ClearCommand::class, QueriesCommand::class]);
        }

        // Eearly return if debugbar can not enabled
        if (!LaravelDebugbar::canBeEnabled()) {
            return;
        }

        if (config('debugbar.options.db.explain.enabled', false)) { // fallback for old config
            config(['debugbar.options.db.explain' => true]);
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
