<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Console;

use Fruitcake\LaravelDebugbar\Console\Concerns\AnalyzesQueries;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Illuminate\Console\Command;

class FindCommand extends Command
{
    use AnalyzesQueries;

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
    {--json : Output as JSON}
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
            $this->option('json') ? $this->line('[]') : $this->info('No results found');
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
        $json = [];
        foreach ($result as $row) {
            unset($row['utime']);

            $data = $storage->get($row['id']);

            $status = $this->requestStatus($data);
            $failed = $this->countFailedQueries($data);

            $summary = [];
            if (isset($data['request']['tooltip']['status'])) {
                $summary[] = $data['request']['tooltip']['status'];
            } elseif ($status !== null) {
                $summary[] = (string) $status;
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
                $summary[] = $data['queries']['nb_statements'] . ' queries in ' . ($data['queries']['accumulated_duration_str'] ?? '?');
            }
            if ($failed > 0) {
                $summary[] = $failed . ' failed ' . ($failed === 1 ? 'query' : 'queries');
            }

            $row['summary'] = implode(', ', $summary);

            $issues = $checkIssues
                ? $this->detectIssues($data, $minQueries, $minDuration, $minDuplicates)
                : [];

            if ($checkIssues) {
                if (count($issues) === 0) {
                    continue;
                }
                $row['issues'] = implode(', ', $issues);
            }

            $rows[] = $row;
            $json[] = [
                'id' => $row['id'],
                'datetime' => $row['datetime'] ?? null,
                'method' => $row['method'] ?? null,
                'uri' => $row['uri'] ?? null,
                'status' => $status,
                'duration_ms' => isset($data['time']['duration']) ? round($data['time']['duration'] * 1000, 2) : null,
                'memory' => $data['memory']['peak_usage_str'] ?? null,
                'queries' => $data['queries']['nb_statements'] ?? 0,
                'failed_queries' => $failed,
                'exceptions' => $data['exceptions']['count'] ?? 0,
                'issues' => $issues,
            ];
        }

        if (count($rows) === 0) {
            if ($this->option('json')) {
                $this->line('[]');
                return;
            }

            $this->info($checkIssues ? 'No issues found in ' . count($result) . ' scanned requests.' : 'No results found');
            return;
        }

        if ($this->option('json')) {
            $this->line((string) json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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
        $status = $this->requestStatus($data);
        if ($status !== null && $status >= 400) {
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
        $dupGroups = count($this->duplicateGroups($data['queries']['statements'] ?? []));
        if ($minDuplicates !== null && $dupGroups >= $minDuplicates) {
            $issues[] = "{$dupGroups} duplicate group(s)";
        }

        // Repeated query shapes with varying bindings, the classic N+1
        $nPlusOne = count($this->nPlusOneGroups($data['queries']['statements'] ?? []));
        if ($minDuplicates !== null && $nPlusOne >= $minDuplicates) {
            $issues[] = "{$nPlusOne} N+1 group(s)";
        }

        // Slow request duration
        $duration = $data['time']['duration'] ?? null;
        if ($minDuration !== null && $duration !== null && ($duration * 1000) >= $minDuration) {
            $durationStr = $data['time']['duration_str'] ?? round($duration * 1000) . 'ms';
            $issues[] = "slow ({$durationStr})";
        }

        // Failed queries
        $failedCount = $this->countFailedQueries($data);
        if ($failedCount > 0) {
            $issues[] = "{$failedCount} failed " . ($failedCount === 1 ? 'query' : 'queries');
        }

        return $issues;
    }

    /**
     * The status code lives in the request collector; `__meta` never carries one.
     */
    private function requestStatus(array $data): ?int
    {
        $status = $data['request']['data']['status_code']
            ?? $data['request']['badge']
            ?? $data['__meta']['status']
            ?? null;

        if ($status === null && isset($data['request']['tooltip']['status'])) {
            // The tooltip holds a rendered "404 Not Found"
            $status = strtok((string) $data['request']['tooltip']['status'], ' ');
        }

        return is_numeric($status) ? (int) $status : null;
    }

    /**
     * `nb_failed_statements` is not always populated, so fall back to the statements.
     */
    private function countFailedQueries(array $data): int
    {
        $failed = (int) ($data['queries']['nb_failed_statements'] ?? 0);
        if ($failed > 0) {
            return $failed;
        }

        return count($this->failedStatements($data['queries']['statements'] ?? []));
    }
}
