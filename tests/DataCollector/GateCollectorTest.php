<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use Fruitcake\LaravelDebugbar\Tests\Models\User;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use DebugBar\DataFormatter\DataFormatter;
use Illuminate\Support\Facades\Gate;

class GateCollectorTest extends TestCase
{
    public function testItCollectsGateChecks()
    {
        debugbar()->boot();

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\GateCollector $collector */
        $collector = debugbar()->getCollector('gate');
        $collector->setDataFormatter(new DataFormatter());

        $user = new User([
            'id' => 1,
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password',
        ]);

        $user->can('view', $user);

        Gate::before(function ($user, $ability, $result, $arguments = []) {
            return true;
        });

        $user->can('view', $user);

        $collect = $collector->collect();
        static::assertEquals(2, $collect['count']);

        $gateError = $collect['messages'][0];
        static::assertEquals('error', $gateError['label']);
        static::assertEquals(
            'view Fruitcake\LaravelDebugbar\Tests\Models\User(id=1)',
            $gateError['message'],
        );
        static::assertEquals(
            [
                'ability' => 'view',
                'target' => 'Fruitcake\LaravelDebugbar\Tests\Models\User(id=1)',
                'result' => 'NULL',
                'user' => '1',
                'arguments' => 'array:1 [
  0 => "Fruitcake\LaravelDebugbar\Tests\Models\User(id=1)"
]',
            ],
            $gateError['context']
        );

        $gateSuccess = $collect['messages'][1];
        static::assertEquals('success', $gateSuccess['label']);
        static::assertEquals(
            'view Fruitcake\LaravelDebugbar\Tests\Models\User(id=1)',
            $gateSuccess['message'],
        );
        static::assertEquals(
            $gateSuccess['context'],
            [
                'ability' => 'view',
                'target' => 'Fruitcake\LaravelDebugbar\Tests\Models\User(id=1)',
                'result' => 'true',
                'user' => '1',
                'arguments' => 'array:1 [
  0 => "Fruitcake\LaravelDebugbar\Tests\Models\User(id=1)"
]',
            ],
        );
    }
}
