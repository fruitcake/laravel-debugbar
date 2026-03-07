<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests;

use Fruitcake\LaravelDebugbar\Facades\Debugbar;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\ServiceProvider;
use Illuminate\Routing\Router;
use Orchestra\Testbench\TestCase as Orchestra;
use Fruitcake\LaravelDebugbar\Tests\Mocks\MockController;
use Fruitcake\LaravelDebugbar\Tests\Mocks\MockViewComponent;
use Fruitcake\LaravelDebugbar\Tests\Mocks\MockMiddleware;
use ReflectionObject;

class TestCase extends Orchestra
{
    /**
     * Get package providers.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }

    /**
     * Get package aliases.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return ['Debugbar' => Debugbar::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        /** @var Router $router */
        $router = $app['router'];
        $app['config']->set('debugbar.enabled', false);
        $app['config']->set('debugbar.hide_empty_tabs', false);
        $app['config']->set('app.debug', true);
        $app['env'] = 'local';

        $this->addWebRoutes($router);
        $this->addApiRoutes($router);
        $this->addViewPaths();
    }

    protected function addWebRoutes(Router $router)
    {
        $router->get('web/plain', function () {
            return 'PONG';
        });

        $router->get('web/empty', function () {
            return '';
        });

        $router->get('web/null', function () {
            return null;
        });

        $router->get('web/html', function () {
            return '<html><head></head><body>Pong</body></html>';
        });

        $router->get('web/fakejson', function () {
            return '{"foo":"bar"}';
        });

        $router->get('web/show', [MockController::class, 'show']);

        $router->get('web/view', MockViewComponent::class);

        $router->post('web/mw')->middleware(MockMiddleware::class);
    }

    protected function addApiRoutes(Router $router)
    {
        $router->get('api/ping', [
            'uses' => function () {
                return response()->json(['status' => 'pong']);
            },
        ]);
    }

    protected function addViewPaths()
    {
        config(['view.paths' => array_merge(config('view.paths'), [__DIR__ . '/resources/views'])]);
    }

    protected function resetStorageOpen(): void
    {
        $debugbar = app(LaravelDebugbar::class);
        (new ReflectionObject($debugbar))
            ->getProperty('storageOpen')
            ->setValue($debugbar, null);
    }
}
