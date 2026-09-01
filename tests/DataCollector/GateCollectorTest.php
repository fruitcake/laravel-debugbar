<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use Fruitcake\LaravelDebugbar\Tests\Models\Person;
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

    public function testItStringifiesModelsPassedAsContextArguments()
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

        $context = new Person([
            'id' => 2,
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => 'password',
        ]);

        // Laravel's documented shape for authorizing creation of a not-yet-existing
        // resource: arguments[0] is the policy class, arguments[1] the context model.
        $user->can('create', [User::class, $context]);

        $message = $collector->collect()['messages'][0];

        static::assertEquals('create Fruitcake\LaravelDebugbar\Tests\Models\User', $message['message']);
        static::assertEquals('Fruitcake\LaravelDebugbar\Tests\Models\User', $message['context']['target']);
        // The context model must be stored stringified, never as a live reference.
        static::assertEquals(
            'array:2 [
  0 => "Fruitcake\LaravelDebugbar\Tests\Models\User"
  1 => "Fruitcake\LaravelDebugbar\Tests\Models\Person(id=2)"
]',
            $message['context']['arguments']
        );
    }
}
