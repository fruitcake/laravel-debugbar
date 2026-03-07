<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Controllers;

use Fruitcake\LaravelDebugbar\Tests\TestCase;

class AssetControllerTest extends TestCase
{
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('app.debug', true);
        $app['config']->set('debugbar.enabled', true);
    }

    public function testAssetRouteRequiresTypeParameter(): void
    {
        $response = $this->getJson('/_debugbar/assets');

        $response->assertUnprocessable();
    }

    public function testAssetRouteRejectsInvalidType(): void
    {
        $response = $this->getJson('/_debugbar/assets?type=invalid');

        $response->assertUnprocessable();
    }

    public function testAssetRouteAcceptsCssType(): void
    {
        $response = $this->get('/_debugbar/assets?type=css');

        $response->assertOk();
    }

    public function testAssetRouteAcceptsJsType(): void
    {
        $response = $this->get('/_debugbar/assets?type=js');

        $response->assertOk();
    }
}
