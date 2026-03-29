<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Console;

use DebugBar\DataCollector\Renderable;
use DebugBar\DataFormatter\VarDumper\DebugBarJsonCaster;
use DebugBar\DataFormatter\VarDumper\DebugBarJsonVar;
use DebugBar\DataFormatter\VarDumper\ReverseJsonDumper;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\VarDumper\Cloner\VarCloner;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class GetCommand extends Command
{
    protected $signature = 'debugbar:get
    {id : The id of the request to show, or "latest" to show the latest}
    {--collector= : Show a specific collector}
    {--raw : Show raw JSON data}
    ';
    protected $description = 'List the Debugbar Storage';

    public function handle(LaravelDebugbar $debugbar): void
    {
        $debugbar->boot();
        $storage = $debugbar->getStorage();
        if (!$storage) {
            $this->error('No Debugbar Storage found..');
        }

        $id = $this->argument('id');
        if ($id === 'latest') {
            $latest = $storage->find([], 1);
            $id = $latest[0]['id'] ?? null;
        }

        $result = $storage->get($id);
        $collector = $this->option('collector');
        if ($collector) {
            $result = $result[$collector] ?? null;
            if (!$result) {
                $this->error('No data found for collector ' . $collector);
                return;
            }
        }

        if ($this->option('raw')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
        } elseif ($this->option('collector')) {
            $this->dumpResult($result);
        } else {
            $this->showSummary($result);
        }
    }

    private function showSummary(array $result): void
    {
        $meta = $result['__meta'];
        unset($meta['utime']);

        $this->table(array_keys($meta), [$meta]);

        $rows = [];
        foreach ($result as $name => $data) {
            if (!is_array($data) || $name === '__meta') {
                continue;
            }

            $badge = $data['count'] ?? '';
            if (debugbar()->hasCollector($name)) {
                $collector = debugbar()->getCollector($name);
                if ($collector instanceof Renderable) {
                    $widgets = $collector->getWidgets();
                    if (isset($widgets[ $collector->getName() . ':badge']['map'])) {
                        $badge = Arr::get($result, $widgets[ $collector->getName() . ':badge']['map'], $badge);
                    }
                }
            }

            $summary = match ($name) {
                'request' => $data['tooltip'] ?? null,
                default => '',
            };

            $summary = $summary ? $this->dumpResult($summary, true) : '';

            $rows[] = [$name, $badge, $summary];
        }

        $this->table(['Collector', 'Badge', 'Summary'], $rows);
    }

    public function dumpResult(array $result, $output = null): ?string
    {
        $reverseFormatter = new ReverseJsonDumper();
        $result = $this->wrapJsonDumps($result, $reverseFormatter);

        $cloner = new VarCloner();
        $cloner->addCasters(DebugBarJsonCaster::getCasters());
        $data = $cloner->cloneVar($result);

        $dumper = new CliDumper();
        return $dumper->dump($data, $output);
    }

    private function wrapJsonDumps(mixed $data, ReverseJsonDumper $formatter): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        // Wrap the data in a special format that the DebugBarJsonCaster can understand
        if (isset($data['_sd']) && $data['_sd'] === 1) {
            return new DebugBarJsonVar($data);
        }

        foreach ($data as $key => $value) {
            $data[$key] = $this->wrapJsonDumps($value, $formatter);
        }

        return $data;
    }
}
