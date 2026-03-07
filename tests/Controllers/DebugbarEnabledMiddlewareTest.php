<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Controllers;

use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use ReflectionObject;

class DebugbarEnabledMiddlewareTest extends TestCase
{
    public function testRoutesReturn404WhenDebugbarIsDisabled(): void
    {
        $this->get('/_debugbar/open')->assertNotFound();
        $this->get('/_debugbar/assets?type=js')->assertNotFound();
        $this->postJson('/_debugbar/queries/explain')->assertNotFound();
        $this->delete('/_debugbar/cache/test-key')->assertNotFound();
    }

    public function testRoutesAreAccessibleWhenDebugbarIsEnabled(): void
    {
        $this->enableDebugbar();

        // Assets route should work (or return validation error, not 404)
        $this->get('/_debugbar/assets?type=js')->assertOk();

        // OpenHandler should be accessible (not 404)
        $response = $this->get('/_debugbar/open');
        static::assertNotEquals(404, $response->getStatusCode());
    }

    protected function enableDebugbar(): void
    {
        $debugbar = app(LaravelDebugbar::class);
        (new ReflectionObject($debugbar))
            ->getProperty('enabled')
            ->setValue($debugbar, true);
    }
}
