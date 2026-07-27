<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use Fruitcake\LaravelDebugbar\DataCollector\InertiaCollector;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\View\View;
use Mockery;

class InertiaCollectorTest extends TestCase
{
    public function testNestedInertiaPropsAreNotTruncated(): void
    {
        $collector = new InertiaCollector();

        $view = Mockery::mock(View::class);

        $view->shouldReceive('getData')->once()->andReturn([
            'page' => [
                'component' => 'Users/Index',
                'props' => [
                    'users' => [
                        [
                            'profile' => [
                                'name' => 'John Doe',
                            ],
                        ],
                    ],
                    'string' => 'value',
                    'integer' => 42,
                    'null' => null,
                    'boolean' => true,
                    'object' => (object) [
                        'name' => 'Jane Doe',
                    ],
                ],
            ],
        ]);

        $view->shouldReceive('getName')->once()->andReturn('app');
        $view->shouldReceive('getPath')->once()->andReturn(null);

        $collector->addFromView($view);

        $params = $collector->collect()['templates'][0]['params'];

        $this->assertStringContainsString('John Doe', $params['users']);
        $this->assertSame('value', $params['string']);
        $this->assertSame('42', $params['integer']);
        $this->assertSame('NULL', $params['null']);
        $this->assertSame('true', $params['boolean']);
        $this->assertStringContainsString('Jane Doe', $params['object']);
    }
}
