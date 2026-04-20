<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use DebugBar\DataFormatter\HtmlDataFormatter;
use Fruitcake\LaravelDebugbar\DataCollector\RouteCollector;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use DebugBar\DataCollector\DataCollector;

class RouteCollectorTest extends TestCase
{
    /** @var \Fruitcake\LaravelDebugbar\DataCollector\RouteCollector $collector */
    private DataCollector $routeCollector;
    protected function setUp(): void
    {
        parent::setUp();
        debugbar()->boot();

        $this->routeCollector = debugbar()->getCollector('route');
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('debugbar.collectors.route', true);

        parent::getEnvironmentSetUp($app);
    }

    public function testItCollectsRouteUri()
    {
        $this->get('web/html');
        static::assertSame('GET web/html', $this->routeCollector->collect()['uri']);

        $this->call('POST', 'web/mw');
        static::assertSame('POST web/mw', $this->routeCollector->collect()['uri']);
    }

    /**
     * @dataProvider controllerData
     */
    public function testItCollectsWithControllerHandler($controller, $file, $url)
    {
        $this->get('web/show');

        $collected = $this->routeCollector->collect();

        static::assertNotEmpty($collected);
        static::assertArrayHasKey('file', $collected);
        static::assertArrayHasKey('controller', $collected);
        static::assertStringContainsString($file, $collected['file']['value']);
        static::assertStringContainsString($url, $collected['file']['xdebug_link']['url']);
        static::assertStringContainsString($controller, $collected['controller']['value']);
        static::assertStringContainsString($url, $collected['controller']['xdebug_link']['url']);
    }

    /**
     * @dataProvider viewComponentData
     */
    public function testItCollectsWithViewComponentHandler($controller, $file, $url)
    {
        $this->get('web/view');

        $collected = $this->routeCollector->collect();

        static::assertStringContainsString($file, $collected['file']['value']);
        static::assertStringContainsString($url, $collected['file']['xdebug_link']['url']);
        static::assertStringContainsString($controller, $collected['controller']['value']);
        static::assertStringContainsString($url, $collected['controller']['xdebug_link']['url']);
    }

    /**
     * @dataProvider closureData
     */
    public function testItCollectsWithClosureHandler($file)
    {
        RouteCollector::setDefaultDataFormatter(new HtmlDataFormatter());

        $this->get('web/html');

        $collected = $this->routeCollector->collect();

        static::assertNotEmpty($collected);
        static::assertArrayHasKey('uses', $collected);
        static::assertArrayHasKey('file', $collected);
        static::assertStringContainsString('Closure', $collected['uses']);
        static::assertStringContainsString($file, $collected['file']['value']);
    }

    public function testItCollectsMiddleware()
    {
        $this->call('POST', 'web/mw');

        $collected = $this->routeCollector->collect();

        static::assertNotEmpty($collected);
        static::assertArrayHasKey('middleware', $collected);
        static::assertStringContainsString('MockMiddleware', $collected['middleware']);
    }

    public static function controllerData()
    {
        $filePath = rawurlencode(str_replace('\\', '/', realpath(__DIR__ . '/../Mocks/MockController.php')));
        return [['MockController@show',
            'MockController.php',
            sprintf('phpstorm://open?file=%s', $filePath),
        ]];
    }

    public static function viewComponentData()
    {
        $filePath = rawurlencode(str_replace('\\', '/', realpath(__DIR__ . '/../Mocks/MockViewComponent.php')));
        return [['MockViewComponent@render',
            'MockViewComponent.php',
            sprintf('phpstorm://open?file=%s', $filePath),
        ]];
    }

    public static function closureData()
    {
        return [['TestCase.php']];
    }
}
