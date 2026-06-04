<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests;

use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\Str;

class CspNonceTest extends TestCase
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

    public function testItUsesViteCspNonce()
    {
        $nonce = Vite::useCspNonce();

        $crawler = $this->call('GET', 'web/html');

        static::assertTrue(Str::contains($crawler->content(), 'debugbar'));
        static::assertTrue(Str::contains($crawler->content(), 'nonce="' . $nonce . '"'));
    }

    public function testItUsesSpatieCspNonce()
    {
        $this->app->instance('csp-nonce', 'spatie-nonce-value');

        $crawler = $this->call('GET', 'web/html');

        static::assertTrue(Str::contains($crawler->content(), 'debugbar'));
        static::assertTrue(Str::contains($crawler->content(), 'nonce="spatie-nonce-value"'));
    }

    public function testItPrefersViteNonceOverSpatieNonce()
    {
        $viteNonce = Vite::useCspNonce();
        $this->app->instance('csp-nonce', 'spatie-nonce-value');

        $crawler = $this->call('GET', 'web/html');

        static::assertTrue(Str::contains($crawler->content(), 'nonce="' . $viteNonce . '"'));
        static::assertFalse(Str::contains($crawler->content(), 'nonce="spatie-nonce-value"'));
    }

    public function testItIgnoresNonStringSpatieNonce()
    {
        $this->app->instance('csp-nonce', ['not', 'a', 'string']);

        $crawler = $this->call('GET', 'web/html');

        static::assertTrue(Str::contains($crawler->content(), 'debugbar'));
        static::assertFalse(Str::contains($crawler->content(), 'nonce='));
    }

    public function testItHasNoNonceWhenNoneIsConfigured()
    {
        $crawler = $this->call('GET', 'web/html');

        static::assertTrue(Str::contains($crawler->content(), 'debugbar'));
        static::assertFalse(Str::contains($crawler->content(), 'nonce='));
    }
}
