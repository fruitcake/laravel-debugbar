<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests;

use Fruitcake\LaravelDebugbar\LaravelDebugbar;

class CanBeEnabledTest extends TestCase
{
    public function testCanBeEnabledInLocalDebugMode()
    {
        // Base TestCase sets env=local, app.debug=true
        static::assertTrue(LaravelDebugbar::canBeEnabled());
    }

    public function testCannotBeEnabledInProduction()
    {
        $this->app['env'] = 'production';

        static::assertFalse(LaravelDebugbar::canBeEnabled());
    }

    public function testCannotBeEnabledInTestingEnvironment()
    {
        $this->app['env'] = 'testing';

        static::assertFalse(LaravelDebugbar::canBeEnabled());
    }

    public function testCannotBeEnabledWithDebugModeOff()
    {
        config(['app.debug' => false]);

        static::assertFalse(LaravelDebugbar::canBeEnabled());
    }

    public function testForceAllowEnableOverridesProductionCheck()
    {
        $this->app['env'] = 'production';
        config(['debugbar.force_allow_enable' => true]);

        static::assertTrue(LaravelDebugbar::canBeEnabled());
    }

    public function testForceAllowEnableOverridesTestingCheck()
    {
        $this->app['env'] = 'testing';
        config(['debugbar.force_allow_enable' => true]);

        static::assertTrue(LaravelDebugbar::canBeEnabled());
    }

    public function testForceAllowEnableOverridesDebugModeOff()
    {
        config(['app.debug' => false]);
        config(['debugbar.force_allow_enable' => true]);

        static::assertTrue(LaravelDebugbar::canBeEnabled());
    }

    public function testForceAllowEnableDefaultsToFalse()
    {
        // Ensure the default is false (not set)
        config(['debugbar.force_allow_enable' => false]);
        $this->app['env'] = 'production';

        static::assertFalse(LaravelDebugbar::canBeEnabled());
    }
}
