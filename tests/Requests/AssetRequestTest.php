<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Requests;

use Fruitcake\LaravelDebugbar\Controllers\AssetController;
use Fruitcake\LaravelDebugbar\Requests\AssetRequest;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use ReflectionMethod;

class AssetRequestTest extends TestCase
{
    public function testItHasExpectedValidationRules(): void
    {
        $request = new AssetRequest();

        static::assertEquals([
            'type' => ['required', 'string', 'in:js,css'],
        ], $request->rules());
    }

    public function testControllerUsesFormRequest(): void
    {
        $method = new ReflectionMethod(AssetController::class, 'getAssets');
        $parameter = $method->getParameters()[0];

        static::assertSame(AssetRequest::class, $parameter->getType()->getName());
    }
}
