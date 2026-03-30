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
    {--issues : Only show requests with potential issues (applies defaults for threshold options)}
    {--min-queries= : Flag requests with at least this many queries (default: 50 with --issues)}
    {--min-duration= : Flag requests slower than this in ms (default: 1000 with --issues)}
    {--min-duplicates= : Flag requests with at least this many duplicate query groups (default: 2 with --issues)}
    ';
    protected $description = 'List the Debugbar Storage';

    public function handle(LaravelDebugbar $debugbar): void
    {
        $debugbar->boot();
        $storage = $debugbar->getStorage();
        if (!$storage) {
            $this->error('No Debugbar Storage found..');
            return;
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

        $hasThresholds = $this->option('min-queries') !== null
            || $this->option('min-duration') !== null
            || $this->option('min-duplicates') !== null;
        $checkIssues = $this->option('issues') || $hasThresholds;

        // Apply defaults when --issues is used, leave null when only specific thresholds are set
        $minQueries = $this->option('min-queries') !== null
            ? (int) $this->option('min-queries')
            : ($this->option('issues') ? 50 : null);
        $minDuration = $this->option('min-duration') !== null
            ? (float) $this->option('min-duration')
            : ($this->option('issues') ? 1000.0 : null);
        $minDuplicates = $this->option('min-duplicates') !== null
            ? (int) $this->option('min-duplicates')
            : ($this->option('issues') ? 2 : null);

        $rows = [];
        foreach ($result as &$row) {
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

            if (isset($data['exceptions']['count']) && $data['exceptions']['count']) {
                $summary[] = $data['exceptions']['count'] . ' exception(s)';
            }
            if (isset($data['queries']['nb_statements'])) {
                $summary[] = $data['queries']['nb_statements'] . ' queries in ' . $data['queries']['accumulated_duration_str'];
            }

            $row['summary'] = implode(', ', $summary);

            if ($checkIssues) {
                $issues = $this->detectIssues($data, $minQueries, $minDuration, $minDuplicates);
                if (count($issues) === 0) {
                    continue;
                }
                $row['issues'] = implode(', ', $issues);
            }

            $rows[] = $row;
        }

        if (count($rows) === 0) {
            $this->info($checkIssues ? 'No issues found in ' . count($result) . ' scanned requests.' : 'No results found');
            return;
        }

        if ($checkIssues) {
            $this->warn(count($rows) . ' of ' . count($result) . ' request(s) with potential issues:');
            $this->newLine();
        }

        $this->table(array_keys($rows[0]), $rows);

        if ($checkIssues) {
            $this->newLine();
            $this->line('Run <fg=cyan>php artisan debugbar:get {id}</> to inspect a request.');
            $this->line('Run <fg=cyan>php artisan debugbar:queries {id}</> to analyze queries.');
        }
    }

    /**
     * @return list<string>
     */
    private function detectIssues(array $data, ?int $minQueries, ?float $minDuration, ?int $minDuplicates): array
    {
        $issues = [];

        // Exceptions
        $exceptionCount = $data['exceptions']['count'] ?? 0;
        if ($exceptionCount > 0) {
            $issues[] = "{$exceptionCount} exception(s)";
        }

        // Non-2xx status
        $status = $data['__meta']['status'] ?? $data['request']['tooltip']['status_code'] ?? null;
        if ($status !== null && (int) $status >= 400) {
            $issues[] = "HTTP {$status}";
        }

        // High query count
        $queryCount = $data['queries']['nb_statements'] ?? 0;
        if ($minQueries !== null && $queryCount >= $minQueries) {
            $issues[] = "{$queryCount} queries";
        }

        // Slow queries
        $slowCount = 0;
        foreach ($data['queries']['statements'] ?? [] as $stmt) {
            if ($stmt['slow'] ?? false) {
                $slowCount++;
            }
        }
        if ($slowCount > 0) {
            $issues[] = "{$slowCount} slow " . ($slowCount === 1 ? 'query' : 'queries');
        }

        // Duplicate query groups
        $dupGroups = $this->countDuplicateGroups($data['queries']['statements'] ?? []);
        if ($minDuplicates !== null && $dupGroups >= $minDuplicates) {
            $issues[] = "{$dupGroups} duplicate group(s)";
        }

        // Slow request duration
        $duration = $data['time']['duration'] ?? null;
        if ($minDuration !== null && $duration !== null && ($duration * 1000) >= $minDuration) {
            $durationStr = $data['time']['duration_str'] ?? round($duration * 1000) . 'ms';
            $issues[] = "slow ({$durationStr})";
        }

        // Failed queries
        $failedCount = $data['queries']['nb_failed_statements'] ?? 0;
        if ($failedCount > 0) {
            $issues[] = "{$failedCount} failed " . ($failedCount === 1 ? 'query' : 'queries');
        }

        return $issues;
    }

    private function countDuplicateGroups(array $statements): int
    {
        $seen = [];
        foreach ($statements as $stmt) {
            if (($stmt['type'] ?? 'query') !== 'query') {
                continue;
            }
            $key = $stmt['sql'] ?? '';
            if (isset($stmt['params']) && count($stmt['params']) > 0) {
                $key .= json_encode($stmt['params']);
            }
            if (isset($stmt['connection'])) {
                $key .= '@' . $stmt['connection'];
            }
            $seen[$key] = ($seen[$key] ?? 0) + 1;
        }

        return count(array_filter($seen, fn(int $count): bool => $count > 1));
    }
}
