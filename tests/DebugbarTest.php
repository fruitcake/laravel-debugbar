<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests;

use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class DebugbarTest extends TestCase
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

        // Force the Debugbar to Enable on test/cli applications
        $app->resolving(LaravelDebugbar::class, function ($debugbar) {
            $refObject = new \ReflectionObject($debugbar);
            $refProperty = $refObject->getProperty('enabled');
            $refProperty->setValue($debugbar, true);
        });
    }

    public function testItInjectsOnPlainText()
    {
        $crawler = $this->call('GET', 'web/plain');

        static::assertTrue(Str::contains($crawler->content(), 'debugbar'));
        static::assertEquals(200, $crawler->getStatusCode());
        static::assertNotEmpty($crawler->headers->get('phpdebugbar-id'));
    }

    public function testItInjectsOnEmptyResponse()
    {
        $crawler = $this->call('GET', 'web/empty');

        static::assertTrue(Str::contains($crawler->content(), 'debugbar'));
        static::assertEquals(200, $crawler->getStatusCode());
        static::assertNotEmpty($crawler->headers->get('phpdebugbar-id'));
    }

    public function testItInjectsOnNullyResponse()
    {
        $crawler = $this->call('GET', 'web/null');

        static::assertTrue(Str::contains($crawler->content(), 'debugbar'));
        static::assertEquals(200, $crawler->getStatusCode());
        static::assertNotEmpty($crawler->headers->get('phpdebugbar-id'));
    }

    public function testItInjectsOnHtml()
    {
        $crawler = $this->call('GET', 'web/html');

        static::assertTrue(Str::contains($crawler->content(), 'debugbar'));
        static::assertEquals(200, $crawler->getStatusCode());
        static::assertNotEmpty($crawler->headers->get('phpdebugbar-id'));
    }

    public function testItDoesntInjectOnJson()
    {
        $crawler = $this->call('GET', 'api/ping');

        static::assertFalse(Str::contains($crawler->content(), 'debugbar'));
        static::assertEquals(200, $crawler->getStatusCode());
        static::assertNotEmpty($crawler->headers->get('phpdebugbar-id'));
    }

    public function testItDoesntInjectOnJsonLookingString()
    {
        $crawler = $this->call('GET', 'web/fakejson');

        static::assertFalse(Str::contains($crawler->content(), 'debugbar'));
        static::assertEquals(200, $crawler->getStatusCode());
        static::assertNotEmpty($crawler->headers->get('phpdebugbar-id'));
    }

    public function testItDoesntInjectsOnHxRequestWithHxTarget()
    {
        $crawler = $this->get('web/html', [
            'Hx-Request' => 'true',
            'Hx-Target' => 'main',
        ]);

        static::assertFalse(Str::contains($crawler->content(), 'debugbar'));
        static::assertEquals(200, $crawler->getStatusCode());
        static::assertNotEmpty($crawler->headers->get('phpdebugbar-id'));
    }

    public function testItInjectsOnHxRequestWithoutHxTarget()
    {
        $crawler = $this->get('web/html', [
            'Hx-Request' => 'true',
        ]);

        static::assertTrue(Str::contains($crawler->content(), 'debugbar'));
        static::assertEquals(200, $crawler->getStatusCode());
        static::assertNotEmpty($crawler->headers->get('phpdebugbar-id'));
    }

    public function testItStoresRequestCorrelationIdFromHeader()
    {
        $meta = $this->collectMetaDataForRequest(
            Request::create('web/html', 'GET', server: ['HTTP_PHPDEBUGBAR_REQUEST_ID' => 'abc-123_x.1'])
        );

        static::assertSame('abc-123_x.1', $meta['rid']);
    }

    public function testItSanitizesRequestCorrelationId()
    {
        $meta = $this->collectMetaDataForRequest(
            Request::create('web/html', 'GET', server: ['HTTP_PHPDEBUGBAR_REQUEST_ID' => 'a*b/c<>d'])
        );

        static::assertSame('abcd', $meta['rid']);
    }

    public function testItTruncatesRequestCorrelationId()
    {
        $meta = $this->collectMetaDataForRequest(
            Request::create('web/html', 'GET', server: ['HTTP_PHPDEBUGBAR_REQUEST_ID' => str_repeat('a', 100)])
        );

        static::assertSame(str_repeat('a', 64), $meta['rid']);
    }

    public function testItOmitsRequestCorrelationIdWhenAbsent()
    {
        $meta = $this->collectMetaDataForRequest(Request::create('web/html', 'GET'));

        static::assertArrayNotHasKey('rid', $meta);
    }

    private function collectMetaDataForRequest(Request $request): array
    {
        /** @var LaravelDebugbar $debugbar */
        $debugbar = $this->app->make(LaravelDebugbar::class);
        $debugbar->setRequest($request);

        return $debugbar->collectMetaData();
    }
}
