<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Requests;

use Fruitcake\LaravelDebugbar\Controllers\OpenHandlerController;
use Fruitcake\LaravelDebugbar\Requests\OpenHandlerRequest;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use ReflectionMethod;

class OpenHandlerRequestTest extends TestCase
{
    public function testItHasExpectedValidationRules(): void
    {
        $request = new OpenHandlerRequest();

        static::assertEquals([
            'op' => ['nullable', 'string'],
        ], $request->rules());
    }

    public function testControllerUsesFormRequest(): void
    {
        $method = new ReflectionMethod(OpenHandlerController::class, 'handle');
        $parameter = $method->getParameters()[0];

        static::assertSame(OpenHandlerRequest::class, $parameter->getType()->getName());
    }
}
