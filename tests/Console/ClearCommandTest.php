<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\Console;

use DebugBar\Storage\StorageInterface;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;

class ClearCommandTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('debugbar.enabled', true);
        $app['config']->set('debugbar.storage.enabled', true);
    }

    public function testClearCommandClearsStorage(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->expects($this->once())->method('clear');

        $debugbar = app(LaravelDebugbar::class);
        $debugbar->boot();
        $debugbar->setStorage($storage);

        Artisan::call('debugbar:clear');
        static::assertStringContainsString('Debugbar Storage cleared!', Artisan::output());
    }

    public function testClearCommandHandlesNoStorage(): void
    {
        $debugbar = app(LaravelDebugbar::class);
        $debugbar->boot();
        $debugbar->setStorage(null);

        Artisan::call('debugbar:clear');
        static::assertStringContainsString('No Debugbar Storage found', Artisan::output());
    }

    public function testClearCommandSuppressesNonExistentDirectoryError(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('clear')->willThrowException(
            new \InvalidArgumentException('The "/tmp/nonexistent" directory does not exist.')
        );

        $debugbar = app(LaravelDebugbar::class);
        $debugbar->boot();
        $debugbar->setStorage($storage);

        Artisan::call('debugbar:clear');
        static::assertStringContainsString('Debugbar Storage cleared!', Artisan::output());
    }

    public function testClearCommandRethrowsOtherInvalidArgumentExceptions(): void
    {
        $storage = $this->createMock(StorageInterface::class);
        $storage->method('clear')->willThrowException(
            new \InvalidArgumentException('Some other error')
        );

        $debugbar = app(LaravelDebugbar::class);
        $debugbar->boot();
        $debugbar->setStorage($storage);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Some other error');

        Artisan::call('debugbar:clear');
    }
}
