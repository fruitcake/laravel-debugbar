<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Controllers;

use Fruitcake\LaravelDebugbar\Tests\DebugbarTest;

class AssetControllerTest extends DebugbarTest
{
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
