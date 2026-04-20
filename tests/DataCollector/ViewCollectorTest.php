<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;

class ViewCollectorTest extends TestCase
{
    use RefreshDatabase;

    public function testIdeLinksAreAbsolutePaths()
    {

        debugbar()->boot();

        /** @var \Fruitcake\LaravelDebugbar\DataCollector\ViewCollector $collector */
        $collector = debugbar()->getCollector('views');
        $collector->addView(
            view('dashboard'),
        );

        tap(Arr::first($collector->collect()['templates']), function (array $template) {
            $this->assertEquals(
                'phpstorm://open?file=' . rawurlencode(str_replace('\\', '/', realpath(__DIR__ . '/../resources/views/dashboard.blade.php'))) . '&line=1',
                $template['xdebug_link']['url'],
            );
        });
    }
}
