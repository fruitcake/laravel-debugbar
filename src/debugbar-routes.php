<?php

declare(strict_types=1);

use Fruitcake\LaravelDebugbar\Controllers\AssetController;
use Fruitcake\LaravelDebugbar\Controllers\CacheController;
use Fruitcake\LaravelDebugbar\Controllers\OpenHandlerController;
use Fruitcake\LaravelDebugbar\Controllers\QueriesController;
use Fruitcake\LaravelDebugbar\Controllers\TelescopeController;
use Fruitcake\LaravelDebugbar\Middleware\DebugbarEnabled;

$routeConfig = [
    'prefix' => app('config')->get('debugbar.route_prefix'),
    'domain' => app('config')->get('debugbar.route_domain'),
    'middleware' => array_merge(app('config')->get('debugbar.route_middleware', []), [DebugbarEnabled::class]),
];

app('router')->group($routeConfig, function ($router): void {
    $router->get('open', [OpenHandlerController::class, 'handle'])->name('debugbar.openhandler');
    $router->delete('cache/{key}', [CacheController::class, 'delete'])->where('key', '.*')->name('debugbar.cache.delete');
    $router->post('queries/explain', [QueriesController::class, 'explain'])->name('debugbar.queries.explain');
    $router->get('clockwork/{id}', [OpenHandlerController::class, 'clockwork'])->name('debugbar.clockwork');
    $router->get('assets', [AssetController::class, 'getAssets'])->name('debugbar.assets');

    if (class_exists(\Laravel\Telescope\Telescope::class)) {
        $router->get('telescope/{id}', [TelescopeController::class, 'show'])->name('debugbar.telescope');
    }
});
