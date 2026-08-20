<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Console;

use Fruitcake\LaravelDebugbar\Console\Concerns\AnalyzesQueries;
use Fruitcake\LaravelDebugbar\LaravelDebugbar;
use Fruitcake\LaravelDebugbar\Support\Explain;
use Illuminate\Console\Command;

class QueriesCommand extends Command
{
    use AnalyzesQueries;

    protected $signature = 'debugbar:queries
    {id : The id of the request to show, or "latest" to show the latest}
    {--statement= : The index of the statement to show}
    {--explain : Run EXPLAIN on the statement (requires --statement)}
    {--result : Run the query and show results (requires --statement)}
    {--json : Output as JSON}
    ';
    protected $description = 'Shows Debugbar Queries from a specific request';

    public function handle(LaravelDebugbar $debugbar): void
    {
        $debugbar->boot();
        $storage = $debugbar->getStorage();
        if (!$storage) {
            $this->error('No Debugbar Storage found..');
            return;
        }

        $id = $this->argument('id');
        if ($id === 'latest') {
            $latest = $storage->find([], 1);
            $id = $latest[0]['id'] ?? null;
            if ($id === null) {
                $this->error('No requests in the Debugbar Storage yet.');
                return;
            }
        }

        $data = $storage->get($id);
        if (!$data) {
            $this->error("Request {$id} not found. Run `php artisan debugbar:find` to list stored requests.");
            return;
        }

        $queries = $data['queries'] ?? [];
        $statements = $queries['statements'] ?? [];

        if (count($statements) === 0) {
            if ($this->option('json')) {
                $this->line('{"statements":[]}');
                return;
            }

            $this->info('No queries found for request ' . $id);
            return;
        }

        $statementIndex = $this->option('statement');

        if ($statementIndex === null && ($this->option('explain') || $this->option('result'))) {
            $this->error('--explain and --result require --statement=N. Run without them to list the statements first.');
            return;
        }

        if (!$this->option('json')) {
            $this->info('Showing queries for request ' . $id);
        }

        if ($statementIndex !== null) {
            $stmt = $statements[(int) $statementIndex] ?? null;
            if (!$stmt) {
                $this->error("Statement #{$statementIndex} not found. Valid range: 0-" . (count($statements) - 1));
                return;
            }

            if ($this->option('json')) {
                $this->line((string) json_encode($stmt, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
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

            $this->showStatementDetail($statements, (int) $statementIndex);
            return;
        }

        if ($this->option('json')) {
            $this->line((string) json_encode($this->summaryData($queries), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            return;
        }

        $this->showSummary($queries);

        $this->info('Run "php artisan debugbar:queries ' . $id . ' --statement=N" to show details for statement # N');
    }

    private function showSummary(array $queries): void
    {
        $failed = $this->failedStatements($queries['statements']);

        $this->info(sprintf(
            '  %d statements | %s total | %d failed',
            $queries['nb_statements'] ?? 0,
            $queries['accumulated_duration_str'] ?? '0ms',
            count($failed),
        ));
        $this->newLine();

        $dupGroups = $this->duplicateGroups($queries['statements']);

        // Map statement index to duplicate count
        $dupCounts = [];
        foreach ($dupGroups as $indices) {
            foreach ($indices as $idx) {
                $dupCounts[$idx] = count($indices);
            }
        }

        $rows = [];
        foreach ($queries['statements'] as $i => $stmt) {
            $dup = $dupCounts[$i] ?? '';

            $flags = [];
            if ($stmt['slow'] ?? false) {
                $flags[] = '<fg=red>SLOW</>';
            }
            if (isset($failed[$i])) {
                $flags[] = '<fg=red>FAILED</>';
            }

            $rows[] = [
                $i,
                $stmt['connection'] ?? '',
                $stmt['type'] ?? 'query',
                $this->truncateSql($stmt['sql'] ?? '', 140),
                $stmt['duration_str'] ?? '',
                implode(' ', $flags),
                $dup ? "<fg=yellow>{$dup}x</>" : '',
                $stmt['filename'] ?? '',
            ];
        }

        $this->table(['#', 'Conn', 'Type', 'SQL', 'Duration', 'Flags', 'Dup', 'Source'], $rows);

        if ($failed) {
            $this->newLine();
            $this->warn('  ' . count($failed) . ' failed ' . (count($failed) === 1 ? 'query' : 'queries') . ':');
            $this->newLine();

            $failedRows = [];
            foreach ($failed as $i => $stmt) {
                $failedRows[] = [
                    $i,
                    $stmt['error_code'] ?? '',
                    $this->truncateSql($stmt['error_message'] ?? '', 160),
                ];
            }
            $this->table(['#', 'Code', 'Error'], $failedRows);
        }

        // Show duplicates summary
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
                    $this->truncateStatementList($indices),
                    $this->truncateSql($stmt['sql'] ?? '', 120),
                ];
            }
            $this->table(['Count', 'Statements', 'SQL'], $dupRows);
        }

        $shapes = $this->nPlusOneGroups($queries['statements']);
        if ($shapes) {
            $this->newLine();
            $this->warn('  ' . count($shapes) . ' repeated query shape(s) with varying bindings (possible N+1):');
            $this->newLine();

            $shapeRows = [];
            foreach ($shapes as $indices) {
                $stmt = $queries['statements'][$indices[0]];
                $shapeRows[] = [
                    count($indices) . 'x',
                    $this->truncateStatementList($indices),
                    $this->truncateSql($stmt['sql'] ?? '', 120),
                ];
            }
            $this->table(['Count', 'Statements', 'SQL'], $shapeRows);
        }
    }

    /**
     * @param list<int> $indices
     */
    private function truncateStatementList(array $indices): string
    {
        if (count($indices) <= 8) {
            return implode(', ', $indices);
        }

        return implode(', ', array_slice($indices, 0, 8)) . ', … (+' . (count($indices) - 8) . ')';
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

        if (isset($stmt['error_message'])) {
            $this->newLine();
            $this->error('FAILED: ' . ($stmt['error_code'] ?? '') . ' ' . $stmt['error_message']);
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

    /**
     * Machine readable version of the summary, for --json.
     */
    private function summaryData(array $queries): array
    {
        $statements = $queries['statements'];
        $groups = $this->duplicateGroups($statements);
        $dupCounts = [];
        foreach ($groups as $indices) {
            foreach ($indices as $idx) {
                $dupCounts[$idx] = count($indices);
            }
        }

        $failed = $this->failedStatements($statements);

        return [
            'nb_statements' => $queries['nb_statements'] ?? 0,
            'accumulated_duration' => $queries['accumulated_duration'] ?? null,
            'accumulated_duration_str' => $queries['accumulated_duration_str'] ?? null,
            'nb_failed_statements' => count($failed),
            'n_plus_one_groups' => array_values(array_map(
                fn(array $indices): array => [
                    'count' => count($indices),
                    'statements' => $indices,
                    'sql' => $statements[$indices[0]]['sql'] ?? '',
                ],
                $this->nPlusOneGroups($statements)
            )),
            'duplicate_groups' => array_values(array_map(
                fn(array $indices): array => [
                    'count' => count($indices),
                    'statements' => $indices,
                    'sql' => $statements[$indices[0]]['sql'] ?? '',
                ],
                $groups
            )),
            'statements' => (array_map(
                fn(int $i): array => [
                    'index' => $i,
                    'sql' => $statements[$i]['sql'] ?? '',
                    'type' => $statements[$i]['type'] ?? 'query',
                    'connection' => $statements[$i]['connection'] ?? null,
                    'duration' => $statements[$i]['duration'] ?? null,
                    'duration_str' => $statements[$i]['duration_str'] ?? null,
                    'slow' => (bool) ($statements[$i]['slow'] ?? false),
                    'duplicates' => $dupCounts[$i] ?? 1,
                    'source' => $statements[$i]['filename'] ?? null,
                    'error_message' => $statements[$i]['error_message'] ?? null,
                ],
                array_keys($statements)
            )),
        ];
    }

    private function truncateSql(string $sql, int $max): string
    {
        if (mb_strlen($sql) <= $max) {
            return $sql;
        }

        return mb_substr($sql, 0, $max - 3) . '...';
    }

}
