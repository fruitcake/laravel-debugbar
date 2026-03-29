<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Console;

use DebugBar\DataFormatter\VarDumper\ReverseJsonDumper;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Illuminate\Console\Command;

class GetCommand extends Command
{
    protected $signature = 'debugbar:get {id}
    {--collector= : Show a specific collector}
    {--raw : Show raw JSON data}
    {--dump : Dump JSON data}
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

        $result = $storage->get($id);
        $collector = $this->option('collector');
        if ($collector) {
            $result = $result[$collector] ?? null;
            if (!$result) {
                $this->error('No data found for collector '.$collector);
                return;
            }
        }

        $reverseFormatter = new ReverseJsonDumper();

        $result = $this->reverseFormat($result, $reverseFormatter);

        if ($this->option('raw')) {
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
            return;
        } elseif ($this->option('dump') || $this->option('collector')) {
            dump($result);
            return;
        }


        $this->showSummary($result);
    }

    private function showSummary(array $result): void
    {
        $rows = [];
        foreach ($result as $collector => $data) {
            if (!is_array($data)) {
                continue;
            }
            $badge = $data['count'] ?? '';
            $rows[] = [$collector, $badge];
        }

        $this->table(['Collector', 'Badge'], $rows);
    }

    private function reverseFormat(mixed $data, ReverseJsonDumper $formatter): mixed
    {
        if (!is_array($data)) {
            return $data;
        }

        if (isset($data['_sd'])) {
            return $formatter->reverseFormatVar($data);
        }

        foreach ($data as $key => $value) {
            $data[$key] = $this->reverseFormat($value, $formatter);
        }

        return $data;
    }
}
