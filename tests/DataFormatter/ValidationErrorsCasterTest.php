<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataFormatter;

use DebugBar\DataCollector\DataCollector;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;

class ValidationErrorsCasterTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        // Force the Debugbar to boot so registerDataFormatter() runs and installs the casters.
        $app->resolving(LaravelDebugbar::class, function ($debugbar) {
            $refObject = new \ReflectionObject($debugbar);
            $refProperty = $refObject->getProperty('enabled');
            $refProperty->setValue($debugbar, true);
        });
    }

    public function testViewErrorBagMessagesAreNotCutOffByMaxDepth()
    {
        $bag = new ViewErrorBag();
        $bag->put('default', new MessageBag([
            'email' => ['The email field is required.'],
        ]));

        $formatted = DataCollector::getDefaultDataFormatter()->formatVar($bag);

        $json = json_encode($formatted);

        static::assertStringContainsString('The email field is required.', $json);
        static::assertStringNotContainsString('_cut', $json);
    }

    public function testBareMessageBagMessagesAreNotCutOffByMaxDepth()
    {
        $bag = new MessageBag([
            'name' => ['The name field is required.'],
        ]);

        $formatted = DataCollector::getDefaultDataFormatter()->formatVar($bag);

        $json = json_encode($formatted);

        static::assertStringContainsString('The name field is required.', $json);
        static::assertStringNotContainsString('_cut', $json);
    }

    public function testMultipleNamedBagsAreAllFlattened()
    {
        // Laravel's documented pattern for pages with more than one form,
        // e.g. $errors->getBag('login') / withErrors($errors, 'register').
        $bag = new ViewErrorBag();
        $bag->put('login', new MessageBag(['email' => ['These credentials do not match.']]));
        $bag->put('register', new MessageBag(['email' => ['The email has already been taken.']]));

        $formatted = DataCollector::getDefaultDataFormatter()->formatVar($bag);
        $json = json_encode($formatted);

        static::assertStringContainsString('These credentials do not match.', $json);
        static::assertStringContainsString('The email has already been taken.', $json);
        static::assertStringNotContainsString('_cut', $json);
    }

    public function testEmptyViewErrorBagDoesNotError()
    {
        // The default state shared with every view when there are no validation errors.
        $formatted = DataCollector::getDefaultDataFormatter()->formatVar(new ViewErrorBag());

        static::assertIsArray($formatted);
        static::assertStringNotContainsString('_cut', json_encode($formatted));
    }
}
