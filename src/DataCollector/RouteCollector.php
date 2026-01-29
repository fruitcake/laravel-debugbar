<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\DataCollector;

use Closure;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\Renderable;
use Illuminate\Routing\Router;
use Livewire\Mechanisms\HandleComponents\HandleComponents;

/**
 * Based on Illuminate\Foundation\Console\RoutesCommand for Taylor Otwell
 * https://github.com/laravel/framework/blob/master/src/Illuminate/Foundation/Console/RoutesCommand.php
 *
 */
class RouteCollector extends DataCollector implements Renderable
{
    /**
     * {@inheritDoc}
     */
    public function collect(): array
    {
        $route = app(Router::class)->current();
        return $this->getRouteInformation($route);
    }

    /**
     * Get the route information for a given route.
     */
    protected function getRouteInformation(mixed $route): array
    {
        if (!is_a($route, 'Illuminate\Routing\Route')) {
            return [];
        }
        $uri = head($route->methods()) . ' ' . $route->uri();
        $action = $route->getAction();

        $result = [
            'uri' => $uri,
        ];

        $result = array_merge($result, $action);
        $uses = $action['uses'] ?? null;
        $controller = is_string($action['controller'] ?? null) ? $action['controller'] : '';

        if (request()->hasHeader('X-Livewire') && class_exists(HandleComponents::class)) {
            try {
                $componentData = request('components')[0] ?? null;
                if (isset($componentData['snapshot'], $componentData['updates'])) {
                    $snapshot = json_decode($componentData['snapshot'], true);
                    if (count($componentData['updates']) > 0) {
                        $method = $componentData['updates'][array_key_first($componentData['updates'])] ?? null;
                    } else {
                        $method = null;
                    }
                    [$component] = app(HandleComponents::class)->fromSnapshot($snapshot);
                    $result['controller'] = ltrim($component::class, '\\');
                    $reflector = new \ReflectionClass($component);
                    $controller = $component::class . '@' . $method;
                }
            } catch (\Throwable $e) {
                //
            }
        }

        if (str_contains($controller, '@')) {
            [$controller, $method] = explode('@', $controller);
            if (class_exists($controller) && method_exists($controller, $method)) {
                $reflector = new \ReflectionMethod($controller, $method);
            }
            unset($result['uses']);
        } elseif ($uses instanceof \Closure) {
            $reflector = new \ReflectionFunction($uses);
            $result['uses'] = $this->getDataFormatter()->formatVar($uses);
        } elseif (is_string($uses) && str_contains($uses, '@__invoke')) {
            if (class_exists($controller) && method_exists($controller, 'render')) {
                $reflector = new \ReflectionMethod($controller, 'render');
                $result['controller'] = $controller . '@render';
            }
        }

        if (isset($reflector)) {
            $filename = $this->normalizeFilePath($reflector->getFileName());
            $result['file'] = sprintf('%s:%s-%s', $filename, $reflector->getStartLine(), $reflector->getEndLine());

            if ($link = $this->getXdebugLink($reflector->getFileName(), $reflector->getStartLine())) {
                $result['file'] = [
                    'value' => $result['file'],
                    'xdebug_link' => $link,
                ];

                if (isset($result['controller'])) {
                    $result['controller'] = [
                        'value' => $result['controller'],
                        'xdebug_link' => $link,
                    ];
                }
            }
        }

        if ($middleware = $this->getMiddleware($route)) {
            $result['middleware'] = $middleware;
        }

        return array_filter($result);
    }

    /**
     * Get middleware
     */
    protected function getMiddleware(mixed $route): string
    {
        return implode(', ', array_map(function ($middleware): mixed {
            return $middleware instanceof Closure ? 'Closure' : $middleware;
        }, $route->gatherMiddleware()));
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'route';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets(): array
    {
        $widgets = [
            "route" => [
                "icon" => "share-3",
                "widget" => "PhpDebugBar.Widgets.HtmlVariableListWidget",
                "map" => "route",
                "default" => "{}",
            ],
        ];
        return $widgets;
    }
}
