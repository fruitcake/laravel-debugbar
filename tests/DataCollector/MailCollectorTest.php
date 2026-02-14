<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Tests\DataCollector;

use DebugBar\Bridge\Symfony\SymfonyMailCollector;
use DebugBar\DataCollector\TimeDataCollector;
use Fruitcake\LaravelDebugbar\Tests\TestCase;
use Illuminate\Support\Facades\Mail;

class MailCollectorTest extends TestCase
{
    public function testItCollectsSentMails(): void
    {
        debugbar()->boot();

        /** @var SymfonyMailCollector $collector */
        $collector = debugbar()->getCollector('symfonymailer_mails');

        Mail::raw('Test body content', function ($message) {
            $message->to('recipient@example.com')
                ->subject('Test Subject');
        });

        $data = $collector->collect();

        static::assertEquals(1, $data['count']);
        static::assertCount(1, $data['mails']);
        static::assertEquals('Test Subject', $data['mails'][0]['subject']);
        static::assertContains('recipient@example.com', $data['mails'][0]['to']);
    }

    public function testItCollectsMultipleMails(): void
    {
        debugbar()->boot();

        /** @var SymfonyMailCollector $collector */
        $collector = debugbar()->getCollector('symfonymailer_mails');

        Mail::raw('First mail', function ($message) {
            $message->to('first@example.com')
                ->subject('First Subject');
        });

        Mail::raw('Second mail', function ($message) {
            $message->to('second@example.com')
                ->subject('Second Subject');
        });

        $data = $collector->collect();

        static::assertEquals(2, $data['count']);
        static::assertCount(2, $data['mails']);
        static::assertEquals('First Subject', $data['mails'][0]['subject']);
        static::assertEquals('Second Subject', $data['mails'][1]['subject']);
    }

    public function testItAddsMailsToTimelineCollector(): void
    {
        debugbar()->boot();

        /** @var TimeDataCollector $timeCollector */
        $timeCollector = debugbar()->getTimeCollector();

        Mail::raw('Timeline test body', function ($message) {
            $message->to('timeline@example.com')
                ->subject('Timeline Test');
        });

        $data = $timeCollector->collect();

        $mailMeasures = array_filter($data['measures'], function ($measure) {
            return str_starts_with($measure['label'], 'Mail: ');
        });

        static::assertNotEmpty($mailMeasures, 'Expected a mail measure in the timeline');

        $mailMeasure = reset($mailMeasures);
        static::assertEquals('Mail: Timeline Test', $mailMeasure['label']);
        static::assertGreaterThan(0, $mailMeasure['duration']);
    }

    public function testItDoesNotAddToTimelineWhenDisabled(): void
    {
        $this->app['config']->set('debugbar.options.mail.timeline', false);

        debugbar()->boot();

        /** @var TimeDataCollector $timeCollector */
        $timeCollector = debugbar()->getTimeCollector();

        Mail::raw('No timeline body', function ($message) {
            $message->to('notimeline@example.com')
                ->subject('No Timeline Test');
        });

        $data = $timeCollector->collect();

        $mailMeasures = array_filter($data['measures'], function ($measure) {
            return str_starts_with($measure['label'], 'Mail: ');
        });

        static::assertEmpty($mailMeasures, 'Expected no mail measures in the timeline');
    }

    public function testItCollectsMailBody(): void
    {
        debugbar()->boot();

        /** @var SymfonyMailCollector $collector */
        $collector = debugbar()->getCollector('symfonymailer_mails');

        Mail::raw('This is the plain text body', function ($message) {
            $message->to('body@example.com')
                ->subject('Body Test');
        });

        $data = $collector->collect();

        static::assertEquals(1, $data['count']);
        static::assertStringContainsString('This is the plain text body', $data['mails'][0]['body']);
    }

    public function testItHidesMailBodyWhenDisabled(): void
    {
        $this->app['config']->set('debugbar.options.mail.show_body', false);

        debugbar()->boot();

        /** @var SymfonyMailCollector $collector */
        $collector = debugbar()->getCollector('symfonymailer_mails');

        Mail::raw('Hidden body content', function ($message) {
            $message->to('nobody@example.com')
                ->subject('Hidden Body Test');
        });

        $data = $collector->collect();

        static::assertEquals(1, $data['count']);
        static::assertNull($data['mails'][0]['body']);
        static::assertNull($data['mails'][0]['html']);
    }
}
