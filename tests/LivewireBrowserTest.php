<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests;

use Fruitcake\LaravelDebugbar\ServiceProvider;
use Fruitcake\LaravelDebugbar\Tests\DataCollector\Livewire\DummyComponent;
use Illuminate\Routing\Router;
use Laravel\Dusk\Browser;
use Livewire\Livewire;
use Livewire\LivewireServiceProvider;

class LivewireBrowserTest extends BrowserTestCase
{
    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['env'] = 'local';
        $app['config']->set('app.debug', true);
        $app['config']->set('debugbar.hide_empty_tabs', false);
        config(['view.paths' => array_merge(config('view.paths'), [__DIR__ . '/resources/views'])]);

        // Set app layout
        config([
            'livewire.layout' => 'layouts.app',    // Livewire 3
            'livewire.component_layout' => 'layouts.app',   // Livewire 4
        ]);

        /** @var Router $router */
        $router = $app['router'];

        // Register Component
        Livewire::component('dummy-component', DummyComponent::class);
        $router->get('web/livewire-component', [
            'uses' => DummyComponent::class,
        ]);

        $router->get('web/livewire-view', function () {
            return view('livewire-component');
        });

        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(\Illuminate\Session\Middleware\StartSession::class);
        $kernel->pushMiddleware(\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class);

        \Orchestra\Testbench\Dusk\Options::withoutUI();
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class, LivewireServiceProvider::class];
    }

    public function testLivewireCollectsComponents()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('web/livewire-component')
                ->waitFor('[wire\\:id]')
                ->waitFor('.phpdebugbar')
                ->click('.phpdebugbar-tab-settings')
                ->waitForTextIn('.phpdebugbar-tab[data-collector="livewire"] .phpdebugbar-badge', 1)
                ->click('.phpdebugbar-tab[data-collector="livewire"]')
                ->assertSee('1 Livewire component')
                ->assertSee('You are #1')
                ->with('.phpdebugbar-widgets-list-item', function ($queriesPane) {
                    $queriesPane->assertSee('DummyComponent')
                        ->click('.phpdebugbar-widgets-name')
                        ->assertSee('Params')
                        ->assertSee('title')
                        ->assertSee('MyComponent');
                })
                ->click('.phpdebugbar-tab[data-collector="request"]')
                ->waitForText('Tests\DataCollector\Livewire\DummyComponent', 3)
                ->clickLink('Increase')
                ->waitForText('You are #2', 30);
        });
    }

    public function testLivewireCollectsView()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('web/livewire-view')
                ->waitFor('.phpdebugbar')
                ->click('.phpdebugbar-tab-settings')
                ->waitForTextIn('.phpdebugbar-tab[data-collector="livewire"] .phpdebugbar-badge', 1)
                ->click('.phpdebugbar-tab[data-collector="livewire"]')
                ->assertSee('1 Livewire component')
                ->assertSee('You are #1')
                ->with('.phpdebugbar-widgets-list-item', function ($queriesPane) {
                    $queriesPane->assertSee('DummyComponent')
                        ->click('.phpdebugbar-widgets-name')
                        ->assertSee('Params')
                        ->assertSee('title')
                        ->assertSee('MyComponent');
                })
                ->click('.phpdebugbar-tab[data-collector="request"]')
                ->clickLink('Increase')
                ->waitForText('You are #2', 3)
                ->assertSee('Tests\DataCollector\Livewire\DummyComponent');
        });
    }
}
