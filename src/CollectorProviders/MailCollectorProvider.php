<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\CollectorProviders;

use DebugBar\Bridge\Symfony\SymfonyMailCollector;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;

class MailCollectorProvider extends AbstractCollectorProvider
{
    public function __invoke(Dispatcher $events, array $options): void
    {
        $mailCollector = new SymfonyMailCollector();
        $this->addCollector($mailCollector);

        $events->listen(function (MessageSent $event) use ($mailCollector): void {
            $mailCollector->addSymfonyMessage($event->sent->getSymfonySentMessage());
        });

        if (($options['show_body'] ?? true) || ($options['full_log'] ?? false)) {
            $mailCollector->showMessageBody();
        }

        if ($options['timeline'] ?? true) {
            $timeCollector = $this->debugbar->getTimeCollector();

            $events->listen(MessageSending::class, fn(MessageSending $e) => $timeCollector->startMeasure('Mail: ' . $e->message->getSubject()));
            $events->listen(MessageSent::class, function (MessageSent $e) use ($timeCollector): void {
                $name = 'Mail: ' . $e->message->getSubject();
                if ($timeCollector->hasStartedMeasure($name)) {
                    $timeCollector->stopMeasure($name);
                } else {
                    $timeCollector->addMeasure($name);
                }
            });
        }
    }
}
