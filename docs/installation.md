---
description: Installing Laravel Debugbar in a project is simple. Use 'composer require fruitcake/laravel-debugbar --dev' to get started now
preview_image: img/preview-install.jpg
---

# Installation

## Install with composer
!!! danger

    Use the Debugbar only in development. Do not use Debugbar on publicly accessible websites, as it will leak information from stored requests (by design).


Require this package with composer. It is recommended to only require the package for development.

```shell
composer require fruitcake/laravel-debugbar --dev
```

Laravel uses Package Auto-Discovery, so doesn't require you to manually add the ServiceProvider.

> If you use a catch-all/fallback route, make sure you load the Debugbar ServiceProvider before your own App ServiceProviders.


## Enable
By default, Debugbar will be enabled when `APP_DEBUG` is `true`.


The profiler is enabled by default, if you have APP_DEBUG=true. You can override that in the config (`debugbar.enabled`) or by setting `DEBUGBAR_ENABLED` in your `.env`. See more options in `config/debugbar.php`

```php
    /*
     |--------------------------------------------------------------------------
     | Debugbar Settings
     |--------------------------------------------------------------------------
     |
     | Debugbar is enabled by default, when debug is set to true in app.php.
     | You can override the value by setting enable to true or false instead of null.
     |
     | You can provide an array of URI's that must be ignored (eg. 'api/*')
     |
     */

    'enabled' => env('DEBUGBAR_ENABLED', null),
    'hide_empty_tabs' => false, // Hide tabs until they have content
    'except' => [
        'telescope*',
        'horizon*',
    ],

```

### Publish config

```shell
php artisan vendor:publish --provider="Fruitcake\LaravelDebugbar\ServiceProvider"
```

## Non-default installs

### Without auto-discovery

If you don't use auto-discovery, add the ServiceProvider to the providers list. For Laravel 11 or newer, add the ServiceProvider in bootstrap/providers.php. For Laravel 10 or older, add the ServiceProvider in config/app.php.

```php
Fruitcake\LaravelDebugbar\ServiceProvider::class,
```

If you want to use the facade to log messages, add this within the `register` method of `app/Providers/AppServiceProvider.php` class:

```php
public function register(): void
{
    $loader = \Illuminate\Foundation\AliasLoader::getInstance();
    $loader->alias('Debugbar', \Fruitcake\LaravelDebugbar\Facades\Debugbar::class);
}
```

### With Octane

Laravel Debugbar 4.x works out of the box with Octane. No need to add anything to your config.

If you're upgrading from Laravel Debugbar 3.x, remove the 'flush' config for Debugbar in `config/octane.php`.

### With Lumen

Lumen is not supported anymore, as it's no longer actively maintained.
