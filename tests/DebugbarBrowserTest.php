<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests;

use Illuminate\Routing\Router;
use Laravel\Dusk\Browser;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Connection;

class DebugbarBrowserTest extends BrowserTestCase
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

        /** @var Router $router */
        $router = $app['router'];

        $this->addWebRoutes($router);
        $this->addApiRoutes($router);
        $this->addViewPaths();

        $kernel = app(\Illuminate\Contracts\Http\Kernel::class);
        $kernel->pushMiddleware(\Illuminate\Session\Middleware\StartSession::class);
        $kernel->pushMiddleware(\Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class);

        \Orchestra\Testbench\Dusk\Options::withoutUI();
    }

    protected function addWebRoutes(Router $router)
    {
        $router->get('web/redirect', [
            'uses' => function () {
                return redirect($this->applicationBaseUrl() . '/web/plain');
            },
        ]);

        $router->get('web/plain', [
            'uses' => function () {
                return 'PONG';
            },
        ]);

        $router->get('web/html', [
            'uses' => function () {
                return '<html><head></head><body>HTMLPONG</body></html>';
            },
        ]);

        $router->get('web/ajax', [
            'uses' => function () {
                return view('ajax');
            },
        ]);

        $router->get('web/custom-prototype', [
            'uses' => function () {

                /** @var Connection $connection */
                $connection = $this->app['db']->connectUsing(
                    'runtime-connection',
                    [
                        'driver' => 'sqlite',
                        'database' => ':memory:',
                    ],
                );
                event(new QueryExecuted('SELECT * FROM users WHERE username = ?', ['debuguser'], 0, $connection));
                return view('custom-prototype');
            },
        ]);

        $router->get('web/query/{num?}', [
            'uses' => function ($num = 1) {
                debugbar()->boot();

                /** @var Connection $connection */
                $connection = $this->app['db']->connectUsing(
                    'runtime-connection',
                    [
                        'driver' => 'sqlite',
                        'database' => ':memory:',
                    ],
                );

                foreach (range(1, $num) as $i) {
                    $executedQuery = new QueryExecuted('SELECT * FROM users WHERE username = ?', ['debuguser' . $i], 0, $connection);
                    event($executedQuery);
                }
                return 'PONG';
            },
        ]);
    }

    protected function addApiRoutes(Router $router)
    {
        $router->get('api/ping', [
            'uses' => function () {
                return response()->json(['status' => 'pong']);
            },
        ]);
    }

    protected function addViewPaths()
    {
        config(['view.paths' => array_merge(config('view.paths'), [__DIR__ . '/resources/views'])]);
    }

    public function testItStacksOnRedirect()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('web/redirect')
                ->assertSee('PONG')
                ->waitFor('.phpdebugbar')
                ->assertSee('GET /web/plain')
                ->click('.phpdebugbar-widgets-datasets-switcher-widget')
                ->waitForTextIn('.phpdebugbar-widgets-datasets-list', 'web/redirect')
                ->assertSee('(stacked)')
                ->assertSee('web/redirect');
        });
    }

    public function testItInjectsOnPlainText()
    {
        $this->browse(function ($browser) {
            $browser->visit('web/plain')
                ->assertSee('PONG')
                ->waitFor('.phpdebugbar')
                ->assertSee('GET /web/plain');
        });
    }

    public function testItInjectsOnHtml()
    {
        $this->browse(function ($browser) {
            $browser->visit('web/html')
                ->assertSee('HTMLPONG')
                ->waitFor('.phpdebugbar')
                ->assertSee('GET /web/html');
        });
    }

    public function testItDoesntInjectOnJson()
    {
        $this->browse(function ($browser) {
            $browser->visit('api/ping')
                ->assertSee('pong')
                ->assertSourceMissing('debugbar')
                ->assertDontSee('GET /api/ping');
        });
    }

    public function testItCapturesAjaxRequests()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('web/ajax')
                ->waitFor('.phpdebugbar')
                ->assertSee('GET /web/ajax')
                ->click('#ajax-link')
                ->waitForTextIn('#result', 'pong')
                ->assertSee('GET /api/ping');
        });
    }

    public function testDatabaseTabIsClickable()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('web/plain')
                ->waitFor('.phpdebugbar')
                ->click('.phpdebugbar-tab-settings')
                ->assertDontSee('0 statements were executed')
                ->click('.phpdebugbar-tab[data-collector="queries"]')
                ->assertSee('0 statements were executed');
        });
    }

    public function testDatabaseCollectsQueries()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('web/query')
                ->waitFor('.phpdebugbar')
                ->click('.phpdebugbar-tab-settings')
                ->waitForTextIn('.phpdebugbar-tab[data-collector="queries"] .phpdebugbar-badge', 1)
                ->click('.phpdebugbar-tab[data-collector="queries"]')
                ->screenshotElement('.phpdebugbar', 'queries-tab')
                ->waitForText('executed')
                ->waitForText('1 statements were executed')
                ->with('.phpdebugbar-widgets-sqlqueries', function ($queriesPane) {
                    $queriesPane->assertSee('SELECT * FROM users')
                        ->click('.phpdebugbar-widgets-list-item:nth-child(2)')
                        ->assertSee('Params')
                        ->assertSee('debuguser')
                        ->assertSee('Backtrace')
                        ->assertSee('DatabaseCollectorProvider.php:');
                })
                ->screenshotElement('.phpdebugbar', 'queries-expanded');
        });
    }

    public function testDatabaseCollectsQueriesWithCustomPrototype()
    {
        if (version_compare($this->app->version(), '10', '<')) {
            static::markTestSkipped('This test is not compatible with Laravel 9.x and below');
        }

        $this->browse(function (Browser $browser) {
            $browser->visit('web/custom-prototype')
                ->waitFor('.phpdebugbar')
                ->click('.phpdebugbar-tab-settings')
                ->waitForTextIn('.phpdebugbar-tab[data-collector="queries"] .phpdebugbar-badge', 1)
                ->click('.phpdebugbar-tab[data-collector="queries"]')
                ->screenshotElement('.phpdebugbar', 'queries-tab')
                ->waitForText('executed')
                ->assertSee('1 statements were executed')
                ->with('.phpdebugbar-widgets-sqlqueries', function ($queriesPane) {
                    $queriesPane->assertSee('SELECT * FROM users')
                        ->click('.phpdebugbar-widgets-list-item:nth-child(2)')
                        ->assertSee('Params')
                        ->assertSee('debuguser')
                        ->assertSee('Backtrace')
                        ->assertSee('DatabaseCollectorProvider.php:');
                })
                ->screenshotElement('.phpdebugbar', 'queries-expanded');
        });
    }

    public function testDatabaseCollectsQueriesWithSoftLimit()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('web/query/200')
                ->waitFor('.phpdebugbar')
                ->click('.phpdebugbar-tab-settings')
                ->waitForTextIn('.phpdebugbar-tab[data-collector="queries"] .phpdebugbar-badge', 200, 30)
                ->click('.phpdebugbar-tab[data-collector="queries"]')
                ->screenshotElement('.phpdebugbar', 'queries-tab')
                ->waitForText('executed')
                ->waitForText('200 statements were executed, 100 of which were duplicates, 100 unique.')
                ->waitForText('Query soft limit for Debugbar is reached after 100 queries, additional 100 queries only show the query.')
                ->screenshotElement('.phpdebugbar', 'queries-expanded');
        });
    }

    public function testDatabaseCollectsQueriesWithHardLimit()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('web/query/600')
                ->waitFor('.phpdebugbar')
                ->click('.phpdebugbar-tab-settings')
                ->waitForTextIn('.phpdebugbar-tab[data-collector="queries"] .phpdebugbar-badge', 600)
                ->click('.phpdebugbar-tab[data-collector="queries"]')
                ->screenshotElement('.phpdebugbar', 'queries-tab')
                ->waitForText('executed')
                ->waitForText('600 statements were executed, 100 have been excluded, 400 of which were duplicates, 200 unique.')
                ->waitForText('Query soft and hard limit for Debugbar are reached. Only the first 100 queries show details. Queries after the first 500 are ignored. ')
                ->screenshotElement('.phpdebugbar', 'queries-expanded');
        });
    }
}
