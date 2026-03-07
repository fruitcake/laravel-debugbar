<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Requests;

use Fruitcake\LaravelDebugbar\Controllers\CacheController;
use Fruitcake\LaravelDebugbar\Requests\CacheDeleteRequest;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use ReflectionMethod;

class CacheDeleteRequestTest extends TestCase
{
    public function testItHasExpectedValidationRules(): void
    {
        $request = new CacheDeleteRequest();

        static::assertEquals([
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string'],
        ], $request->rules());
    }

    public function testControllerUsesFormRequest(): void
    {
        $method = new ReflectionMethod(CacheController::class, 'delete');
        $parameter = collect($method->getParameters())->first(
            fn($p) => $p->getName() === 'request'
        );

        static::assertNotNull($parameter);
        static::assertSame(CacheDeleteRequest::class, $parameter->getType()->getName());
    }
}
