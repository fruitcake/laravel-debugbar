<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\DataCollector;

use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Illuminate\Support\Str;

class LaravelCollector extends DataCollector implements Renderable
{
    /**
     * {@inheritDoc}
     */
    public function collect(): array
    {
        $app = app();
        return [
            "version" => Str::of($app->version())->explode('.')->first() . '.x',
            'tooltip' => array_filter([
                'Laravel Version' => $app->version(),
                'PHP Version' => phpversion(),
                'Environment' => $app->environment(),
                'Debug Mode' => config('app.debug') ? 'Enabled' : 'Disabled',
                'URL' => Str::of(config('app.url'))->replace(['http://', 'https://'], ''),
                'Timezone' => config('app.timezone'),
                'Locale' => config('app.locale'),
                'Cached' => implode(', ', array_filter([
                    $app->configurationIsCached() ? 'Configs' : null,
                    $app->routesAreCached() ? 'Routes' : null,
                    $app->eventsAreCached() ? 'Events' : null,
                ])),
            ]),
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'laravel';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets(): array
    {
        return [
            "version" => [
                "icon" => "brand-laravel",
                "map" => "laravel.version",
                "default" => "",
            ],
            "version:tooltip" => [
                "map" => "laravel.tooltip",
                "default" => "{}",
            ],
            // The tooltip is already the environment at a glance, so it doubles as the summary.
            // Keyed on `laravel` rather than `version` so the summary section is titled "Laravel".
            "laravel:summary" => [
                "map" => "laravel.tooltip",
            ],
        ];
    }
}
