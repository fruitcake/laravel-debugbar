<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use Fruitcake\LaravelDebugbar\Tests\Models\Person;
use Fruitcake\LaravelDebugbar\Tests\Models\User;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class ModelsCollectorTest extends TestCase
{
    use RefreshDatabase;

    public function testItCollectsRetrievedModels()
    {
        $this->loadLaravelMigrations();
        debugbar()->boot();

        /** @var \DebugBar\DataCollector\ObjectCountCollector $collector */
        $collector = debugbar()->getCollector('models');
        $collector->setXdebugLinkTemplate('');
        $collector->collectCountSummary(false);
        $collector->setKeyMap([]);
        $data = [];

        static::assertEquals(
            ['data' => $data, 'key_map' => [], 'count' => 0, 'is_counter' => true],
            $this->countsOnly($collector),
        );

        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
        ]);

        User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => Hash::make('password'),
        ]);

        $data[User::class] = ['created' => 2];
        static::assertEquals(
            [
                'data' => $data,
                'count' => 2,
                'is_counter' => true,
                'key_map' => [
                ],
            ],
            $this->countsOnly($collector),
        );

        $user = User::first();

        $data[User::class]['retrieved'] = 1;
        static::assertEquals(
            ['data' => $data, 'key_map' => [], 'count' => 3, 'is_counter' => true],
            $this->countsOnly($collector),
        );

        $user->update(['name' => 'Jane Doe']);

        $data[User::class]['updated'] = 1;
        static::assertEquals(
            [
                'data' => $data,
                'count' => 4,
                'is_counter' => true,
                'key_map' => [],
            ],
            $this->countsOnly($collector),
        );

        Person::all();

        $data[Person::class] = ['retrieved' => 2];
        static::assertEquals(
            ['data' => $data, 'key_map' => [], 'count' => 6, 'is_counter' => true],
            $this->countsOnly($collector),
        );

        $user->delete();

        $data[User::class]['deleted'] = 1;
        static::assertEquals(
            [
                'data' => $data,
                'count' => 7,
                'is_counter' => true,
                'key_map' => [
                ],
            ],
            $this->countsOnly($collector),
        );
    }

    public function testItSummarizesTheHeaviestModelCounts(): void
    {
        $this->loadLaravelMigrations();
        debugbar()->boot();

        /** @var \DebugBar\DataCollector\ObjectCountCollector $collector */
        $collector = debugbar()->getCollector('models');
        $collector->setXdebugLinkTemplate('');

        static::assertSame([], $collector->collect()['summary'], 'nothing retrieved, nothing to summarize');

        User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => Hash::make('password'),
        ]);

        $summary = $collector->collect()['summary'];

        static::assertSame(1, $summary['count']);
        static::assertContains(User::class . ' = 1', $summary['top']);
    }

    /**
     * The collected data without its summary, which is asserted separately so these
     * exact-shape comparisons stay about the counting behaviour.
     */
    private function countsOnly(\DebugBar\DataCollector\ObjectCountCollector $collector): array
    {
        $data = $collector->collect();
        unset($data['summary']);

        return $data;
    }
}
