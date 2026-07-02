<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Mocks;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class AiMockTool implements Tool
{
    public function description(): Stringable|string
    {
        return 'A mock tool used in tests';
    }

    public function handle(Request $request): Stringable|string
    {
        return 'ok';
    }

    /**
     * @return array<string, mixed>
     */
    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
