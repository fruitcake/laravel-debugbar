<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\DataCollector;

use DebugBar\DataCollector\TimeDataCollector;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

class EventCollector extends TimeDataCollector
{
    protected array $excludedEvents = [];

    protected bool $collectValues = false;

    protected bool $collectListeners = false;

    public function setCollectValues(bool $collectValues = true): void
    {
        $this->collectValues = $collectValues;
    }

    public function setCollectListeners(bool $collectListeners = true): void
    {
        $this->collectListeners = $collectListeners;
    }

    public function setExcludedEvents(array $excludedEvents): void
    {
        $this->excludedEvents = $excludedEvents;
    }

    public function onWildcardEvent(?string $name = null, array $data = []): void
    {
        $currentTime = microtime(true);
        $eventClass = explode(':', $name)[0];

        foreach ($this->excludedEvents as $excludedEvent) {
            if (Str::is($excludedEvent, $eventClass)) {
                return;
            }
        }

        if (! $this->collectValues) {
            $this->addMeasure($name, $currentTime, $currentTime, [], null, $eventClass);

            return;
        }

        $params = $data;

        if ($this->collectListeners) {
            $params['listeners'] = Event::getListeners($name);
        }

        $this->addMeasure($name, $currentTime, $currentTime, $params, null, $eventClass);
    }

    public function collect(): array
    {
        $data = parent::collect();
        $data['nb_measures'] = $data['count'] = count($data['measures']);

        return $data;
    }

    public function getName(): string
    {
        return 'event';
    }

    public function getWidgets(): array
    {
        return [
            "event" => [
                "icon" => "subtask",
                "widget" => "PhpDebugBar.Widgets.TimelineWidget",
                "map" => "event",
                "default" => "{}",
            ],
            'events:badge' => [
                'map' => 'event.nb_measures',
                'default' => 0,
            ],
        ];
    }
}
