<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Requests;

use Fruitcake\LaravelDebugbar\Controllers\QueriesController;
use Fruitcake\LaravelDebugbar\Requests\QueriesExplainRequest;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use ReflectionMethod;

class QueriesExplainRequestTest extends TestCase
{
    public function testItHasExpectedValidationRules(): void
    {
        $request = new QueriesExplainRequest();

        static::assertEquals([
            'connection' => ['required', 'string'],
            'query' => ['required', 'string'],
            'bindings' => ['nullable', 'array'],
            'hash' => ['required', 'string'],
            'mode' => ['nullable', 'string', 'in:visual,result'],
            'format' => ['nullable', 'string'],
        ], $request->rules());
    }

    public function testControllerUsesFormRequest(): void
    {
        $method = new ReflectionMethod(QueriesController::class, 'explain');
        $parameter = $method->getParameters()[0];

        static::assertSame(QueriesExplainRequest::class, $parameter->getType()->getName());
    }
}
