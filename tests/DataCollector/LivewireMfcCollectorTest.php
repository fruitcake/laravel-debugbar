<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use Composer\InstalledVersions;
use Fruitcake\LaravelDebugbar\DataCollector\LivewireCollector;
use Fruitcake\LaravelDebugbar\ServiceProvider;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Livewire\LivewireServiceProvider;

class LivewireMfcCollectorTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class, LivewireServiceProvider::class];
    }

    public function testItLinksToTheSourceOfAMultiFileComponent()
    {
        if (version_compare(InstalledVersions::getVersion('livewire/livewire'), '4.0', '<')) {
            static::markTestSkipped('Multi-file components require Livewire 4.');
        }

        app('livewire.finder')->addLocation(viewPath: __DIR__ . '/Livewire/mfc');

        debugbar()->boot();

        /** @var LivewireCollector $collector */
        $collector = debugbar()->getCollector('livewire');

        $component = app('livewire.factory')->create('counter', '123');

        $collector->addLivewireComponent($component, request());

        $data = $collector->collect();
        $link = $data['templates'][0]['xdebug_link'] ?? null;

        static::assertNotNull($link, 'No source link was collected for the component.');

        $url = urldecode($link['url']);

        static::assertStringContainsString('mfc/counter/counter.php', $url);
        static::assertStringNotContainsString('/livewire/classes/', $url);
    }
}
