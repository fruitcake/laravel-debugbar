<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Console;

use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\Support\Explain;
use Illuminate\Console\Command;

class QueriesCommand extends Command
{
    protected $signature = 'debugbar:queries
    {id : The id of the request to show, or "latest" to show the latest}
    {--statement= : The index of the statement to show}
    {--explain : Run EXPLAIN on the statement (requires --statement)}
    {--result : Run the query and show results (requires --statement)}
    ';
    protected $description = 'Shows Debugbar Queries from a specific request';

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

        $this->info('Showing queries for request ' . $id);

        $queries = $storage->get($id)['queries'] ?? [];

        if (count($queries) === 0 || count($queries['statements']) === 0) {
            $this->info('No queries found');
            return;
        }

        $statementIndex = $this->option('statement');

        if ($statementIndex !== null) {
            $stmt = $queries['statements'][(int) $statementIndex] ?? null;
            if (!$stmt) {
                $this->error("Statement #{$statementIndex} not found. Valid range: 0-" . (count($queries['statements']) - 1));
                return;
            }

            if ($this->option('explain')) {
                $this->runExplain($stmt);
                return;
            }

            if ($this->option('result')) {
                $this->runResult($stmt);
                return;
            }

            $this->showStatementDetail($queries['statements'], (int) $statementIndex);
            return;
        }

        $this->showSummary($queries);

        $this->info('Run "php artisan debugbar:queries ' . $id . ' --statement=N" to show details for statement # N');
    }

    private function showSummary(array $queries): void
    {
        $this->info(sprintf(
            '  %d statements | %s total | %d failed',
            $queries['nb_statements'] ?? 0,
            $queries['accumulated_duration_str'] ?? '0ms',
            $queries['nb_failed_statements'] ?? 0,
        ));
        $this->newLine();

        // Build duplicate index: group by sql+params+connection (matching front-end logic)
        $duplicates = [];
        foreach ($queries['statements'] as $i => $stmt) {
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
            $duplicates[$key] ??= [];
            $duplicates[$key][] = $i;
        }

        // Map statement index to duplicate count
        $dupCounts = [];
        foreach ($duplicates as $indices) {
            if (count($indices) > 1) {
                foreach ($indices as $idx) {
                    $dupCounts[$idx] = count($indices);
                }
            }
        }

        $rows = [];
        foreach ($queries['statements'] as $i => $stmt) {
            $dup = $dupCounts[$i] ?? '';
            $rows[] = [
                $i,
                $stmt['connection'] ?? '',
                $stmt['type'] ?? 'query',
                $this->truncateSql($stmt['sql'] ?? '', 140),
                $stmt['duration_str'] ?? '',
                $stmt['slow'] ? '<fg=red>SLOW</>' : '',
                $dup ? "<fg=yellow>{$dup}x</>" : '',
                $stmt['filename'] ?? '',
            ];
        }

        $this->table(['#', 'Conn', 'Type', 'SQL', 'Duration', 'Slow', 'Dup', 'Source'], $rows);

        // Show duplicates summary
        $dupGroups = array_filter($duplicates, fn($indices) => count($indices) > 1);
        if ($dupGroups) {
            $totalDup = array_sum(array_map('count', $dupGroups));
            $this->newLine();
            $this->warn("  {$totalDup} duplicate queries in " . count($dupGroups) . ' group(s):');
            $this->newLine();

            $dupRows = [];
            foreach ($dupGroups as $indices) {
                $stmt = $queries['statements'][$indices[0]];
                $dupRows[] = [
                    count($indices) . 'x',
                    implode(', ', $indices),
                    $this->truncateSql($stmt['sql'] ?? '', 120),
                ];
            }
            $this->table(['Count', 'Statements', 'SQL'], $dupRows);
        }
    }

    private function showStatementDetail(array $statements, int $index): void
    {
        $stmt = $statements[$index];

        $this->info("Statement #{$index}");
        $this->newLine();
        $this->line('<fg=gray>SQL:</> ' . ($stmt['sql'] ?? ''));

        if (isset($stmt['params']) && count($stmt['params']) > 0) {
            $this->line('<fg=gray>Params:</> ' . json_encode($stmt['params']));
        }

        $this->line('<fg=gray>Type:</> ' . ($stmt['type'] ?? 'query'));
        $this->line('<fg=gray>Connection:</> ' . ($stmt['connection'] ?? ''));
        $this->line('<fg=gray>Duration:</> ' . ($stmt['duration_str'] ?? ''));
        $this->line('<fg=gray>Source:</> ' . ($stmt['filename'] ?? ''));

        if ($stmt['slow'] ?? false) {
            $this->warn('SLOW QUERY');
        }

        if (isset($stmt['backtrace'])) {
            $this->newLine();
            $this->info('Backtrace:');
            $rows = [];
            foreach ($stmt['backtrace'] as $frame) {
                $rows[] = [
                    $frame['index'] ?? '',
                    $frame['name'] ?? '',
                    $frame['line'] ?? '',
                ];
            }
            $this->table(['#', 'File', 'Line'], $rows);
        }

        if (isset($stmt['explain']['modes'])) {
            $this->newLine();
            $runModes = array_map(fn($mode) => '--' . $mode, $stmt['explain']['modes']);
            $this->info('Run this command with ' . implode(' or ', $runModes) . ' to query the database directly.');
        }
    }

    private function runExplain(array $stmt): void
    {
        $explain = app(Explain::class);
        $connection = $stmt['explain']['connection'] ?? $stmt['connection'] ?? '';
        $sql = $stmt['explain']['query'] ?? $stmt['sql'] ?? '';
        $bindings = $stmt['params'] ?? [];
        $hash = $explain->hash($connection, $sql, $bindings);

        if (!$explain->isReadOnlyQuery($sql)) {
            $this->error('Only SELECT queries can be explained.');
            return;
        }

        try {
            $result = $explain->generateRawExplain($connection, $sql, $bindings, $hash);
            $this->info('EXPLAIN for: ' . $sql);
            $this->newLine();

            $rows = array_map(fn($row) => (array) $row, $result);

            if ($rows) {
                $this->table(array_keys($rows[0]), $rows);
            }
        } catch (\Exception $e) {
            $this->error('EXPLAIN failed: ' . $e->getMessage());
        }
    }

    private function runResult(array $stmt): void
    {
        $explain = app(Explain::class);
        $connection = $stmt['explain']['connection'] ?? $stmt['connection'] ?? '';
        $sql = $stmt['explain']['query'] ?? $stmt['sql'] ?? '';
        $bindings = $stmt['params'] ?? [];
        $hash = $explain->hash($connection, $sql, $bindings);

        if (!$explain->isReadOnlyQuery($sql)) {
            $this->error('Only SELECT queries can be executed.');
            return;
        }

        try {
            $data = $explain->generateSelectResult($connection, $sql, $bindings, $hash, null);
            $rows = $data['result'] ?? [];

            $this->info('Result for: ' . $sql);
            $this->newLine();

            if (!$rows) {
                $this->info('No results returned.');
                return;
            }

            $rows = array_map(fn($row) => (array) $row, $rows);
            $this->table(array_keys($rows[0]), $rows);
        } catch (\Exception $e) {
            $this->error('Query failed: ' . $e->getMessage());
        }
    }

    private function truncateSql(string $sql, int $max): string
    {
        if (mb_strlen($sql) <= $max) {
            return $sql;
        }

        return mb_substr($sql, 0, $max - 3) . '...';
    }

}
