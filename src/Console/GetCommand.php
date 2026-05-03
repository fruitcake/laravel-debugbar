<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Console;

use DebugBar\DataCollector\Renderable;
use DebugBar\DataFormatter\VarDumper\ReverseJsonDumper;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\VarDumper\Dumper\CliDumper;

class GetCommand extends Command
{
    protected $signature = 'debugbar:get
    {id : The id of the request to show, or "latest" to show the latest}
    {--collector= : Show a specific collector}
    {--raw : Show raw JSON data}
    ';
    protected $description = 'Shows a Debugbar request from the Storage';

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

            $badge = $data['count'] ?? null;
            if (debugbar()->hasCollector($name)) {
                $collector = debugbar()->getCollector($name);
                if ($collector instanceof Renderable) {
                    $widgets = $collector->getWidgets();
                    if (isset($widgets[ $collector->getName() . ':badge']['map'])) {
                        $badge = Arr::get($result, $widgets[ $collector->getName() . ':badge']['map'], $badge);
                    }
                }
            }

            $plural = match ($name) {
                'caches' => 'cache events',
                'symfonymailer_mails' => 'mails sent',
                'livewire' => 'livewire components',
                'http_client' => 'http requests',
                'session' => 'session values',
                default => Str::plural($name),
            };

            $summary = match ($name) {
                'request' => $data['tooltip'],
                'time' => $data['duration_str'] ?? null,
                'memory' => $data['peak_usage_str'] ?? null,
                'queries' => $data['nb_statements'] . ' queries in ' . $data['accumulated_duration_str'],
                'route' => ($data['as'] ?? '') . ' @ ' . ($data['file']['value'] ?? ''),
                default => $badge !== null ? $badge . ' ' . $plural : null,
            };

            if ($summary && !is_string($summary)) {
                $summary = $this->dumpResult($summary, true);
            }

            $rows[] = [$name, $summary];
        }

        $this->table(['Collector', 'Summary'], $rows);

        if (isset($data['queries'])) {
            $this->line('Run `php artisan debugbar:queries ' . $result['__meta']['id'] . '` to see the query details');
        }
    }

    public function dumpResult(array $result, $output = null): ?string
    {
        $reverseFormatter = new ReverseJsonDumper();
        $data = $reverseFormatter->toCloneVarData($result);

        $dumper = new CliDumper();
        return $dumper->dump($data, $output);
    }
}
