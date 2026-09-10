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
        // Replaces the generic timeline summary from TimeDataCollector.
        $data['summary'] = $this->summarizeEvents($data['measures']);

        return $data;
    }

    /**
     * The events that fired most, which is what you want when something ran too often.
     *
     * @param array<int, array<string, mixed>> $measures
     *
     * @return array<string, mixed>
     */
    protected function summarizeEvents(array $measures, int $max = 10): array
    {
        if (!$measures) {
            return [];
        }

        $counts = [];
        foreach ($measures as $measure) {
            $label = (string) ($measure['label'] ?? '');
            $counts[$label] = ($counts[$label] ?? 0) + 1;
        }

        arsort($counts);
        $summary = ['events' => count($measures), 'distinct' => count($counts)];

        $top = [];
        foreach (array_slice($counts, 0, $max, true) as $label => $count) {
            $top[] = $count . 'x ' . $label;
        }
        $summary['top'] = $top;

        $extra = count($counts) - $max;
        if ($extra > 0) {
            $summary['not_shown'] = $extra;
        }

        return $summary;
    }

    public function getName(): string
    {
        return 'event';
    }

    public function getWidgets(): array
    {
        return [
            "events:summary" => [
                "map" => "event.summary",
            ],
            "events" => [
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
