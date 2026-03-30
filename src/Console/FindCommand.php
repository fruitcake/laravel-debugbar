<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Console;

use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Illuminate\Console\Command;

class FindCommand extends Command
{
    protected $signature = 'debugbar:find
    {--utime= : Shows only requests after this micro timestamp}
    {--ip= : Filter by IP}
    {--method= : Filter by HTTP method (GET/POST/PUT/DELETE)}
    {--uri= : Filter by URI, eg. /admin/*, in fnmatch format}
    {--max=20 : Number of results to show}
    {--offset=0 : Offset of the results}
    ';
    protected $description = 'List the Debugbar Storage';

    public function handle(LaravelDebugbar $debugbar): void
    {
        $debugbar->boot();
        $storage = $debugbar->getStorage();
        if (!$storage) {
            $this->error('No Debugbar Storage found..');
        }

        $filters = [];
        if ($this->option('utime')) {
            $filters['utime'] = (int) $this->option('utime');
        }
        if ($this->option('ip')) {
            $filters['ip'] = $this->option('ip');
        }
        if ($this->option('method')) {
            $filters['method'] = $this->option('method');
        }
        if ($this->option('uri')) {
            $filters['uri'] = $this->option('uri');
        }

        $result = $storage->find(
            $filters,
            (int) $this->option('max'),
            (int) $this->option('offset'),
        );

        if (count($result) === 0) {
            $this->info('No results found');
            return;
        }

        $result = array_map(function ($row): mixed {
            unset($row['utime']);
            return $row;
        }, $result);

        foreach ($result as $i => &$row) {
            unset($row['utime']);

            $data = $storage->get($row['id']);

            $summary = [];
            if (isset($data['request']['tooltip']['status'])) {
                $summary[] = $data['request']['tooltip']['status'];
            }
            if (isset($data['time']['duration_str'], $data['memory']['peak_usage_str'])) {
                $summary[] = $data['time']['duration_str'] . '/' . $data['memory']['peak_usage_str'] . ' request';
            } else {
                if (isset($data['time']['duration_str'])) {
                    $summary[] = $data['time']['duration_str'];
                }
                if (isset($data['memory']['peak_usage_str'])) {
                    $summary[] = $data['memory']['peak_usage_str'];
                }
            }

            if (isset($data['exception']['count']) && $data['exception']['count']) {
                $summary[] = $data['queries']['count'] . ' exception';
            }
            if (isset($data['queries']['count'])) {
                $summary[] = $data['queries']['count'] . ' queries in ' . $data['queries']['accumulated_duration_str'];
            }

            $row['summary'] = implode(', ', $summary);
        }

        $latest = $result[0];
        $this->table(array_keys($latest), $result);
    }
}
