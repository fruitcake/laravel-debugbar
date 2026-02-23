<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\DataCollector;

use DebugBar\DataCollector\Resettable;
use Fruitcake\LaravelDebugbar\Support\Explain;
use DebugBar\DataCollector\AssetProvider;
use DebugBar\DataCollector\DataCollector;
use DebugBar\DataCollector\HasTimeDataCollector;
use DebugBar\DataCollector\Renderable;
use DebugBar\DataFormatter\QueryFormatter;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Database\Query\Grammars\Grammar;
use Illuminate\Support\Str;

/**
 * Collects data about SQL statements executed with PDO
 */
class QueryCollector extends DataCollector implements Renderable, AssetProvider, Resettable
{
    use HasTimeDataCollector;

    protected array $queries = [];
    protected int $queryCount = 0;
    protected int $transactionEventsCount = 0;
    protected int $infoStatements = 0;
    protected ?int $softLimit = null;
    protected ?int $hardLimit = null;
    protected ?int $lastMemoryUsage = null;
    protected bool|int $findSource = false;
    protected array $middleware = [];
    protected bool $explainQuery = false;
    protected array $explainTypes = ['SELECT']; // ['SELECT', 'INSERT', 'UPDATE', 'DELETE']; for MySQL 5.6.3+
    protected array $reflection = [];
    protected array $excludePaths = [];
    protected array $backtraceExcludePaths = [
        '/vendor/laravel/framework/src/Illuminate/Support',
        '/vendor/laravel/framework/src/Illuminate/Database',
        '/vendor/laravel/framework/src/Illuminate/Events',
        '/vendor/laravel/framework/src/Illuminate/Collections',
        '/vendor/october/rain',
        '/vendor/barryvdh/laravel-debugbar',
        '/vendor/fruitcake/laravel-debugbar',
    ];

    protected ?QueryFormatter $queryFormatter = null;
    protected bool $renderSqlWithParams = false;
    protected bool $durationBackground = false;
    protected ?float $slowThreshold = null;

    public function getQueryFormatter(): QueryFormatter
    {
        if ($this->queryFormatter === null) {
            $this->queryFormatter = new QueryFormatter();
        }
        return $this->queryFormatter;
    }

    /**
     * @param int|null $softLimit After the soft limit, no parameters/backtrace are captured
     * @param int|null $hardLimit After the hard limit, queries are ignored
     */
    public function setLimits(?int $softLimit, ?int $hardLimit): void
    {
        $this->softLimit = $softLimit;
        $this->hardLimit = $hardLimit;
    }

    /**
     * Renders the SQL of traced statements with params embedded
     */
    public function setRenderSqlWithParams(bool $enabled = true): void
    {
        $this->renderSqlWithParams = $enabled;
    }

    /**
     * Enable/disable finding the source
     */
    public function setFindSource(bool|int $value, array $middleware): void
    {
        $this->findSource = $value;
        $this->middleware = $middleware;
    }

    public function mergeExcludePaths(array $excludePaths): void
    {
        $this->excludePaths = array_merge($this->excludePaths, $excludePaths);
    }

    /**
     * Set additional paths to exclude from the backtrace
     */
    public function mergeBacktraceExcludePaths(array $excludePaths): void
    {
        $this->backtraceExcludePaths = array_merge($this->backtraceExcludePaths, $excludePaths);
    }

    /**
     * Enable/disable the shaded duration background on queries
     */
    public function setDurationBackground(bool $enabled): void
    {
        $this->durationBackground = $enabled;
    }

    /**
     * Highlights queries that exceed the threshold
     *
     * @param int|float $threshold miliseconds value
     */
    public function setSlowThreshold(int|float $threshold): void
    {
        $this->slowThreshold = $threshold / 1000;
    }

    public function isSqlRenderedWithParams(): bool
    {
        return $this->renderSqlWithParams;
    }

    /**
     * Enable/disable the EXPLAIN queries
     */
    public function setExplainSource(bool $enabled): void
    {
        $this->explainQuery = $enabled;
    }

    public function startMemoryUsage(): void
    {
        $this->lastMemoryUsage = memory_get_usage(false);
    }

    public function addQuery(QueryExecuted $query): void
    {
        $this->queryCount++;

        if ($this->hardLimit && $this->queryCount > $this->hardLimit) {
            return;
        }

        $limited = $this->softLimit && $this->queryCount > $this->softLimit;

        $sql = $query->sql;
        $time = $query->time / 1000;
        $endTime = microtime(true);
        $startTime = $endTime - $time;

        $source = [];

        if (!$limited && $this->findSource) {
            try {
                $source = $this->findSource();
            } catch (\Exception $e) {
            }
        }

        $bindings = match (true) {
            $limited && filled($query->bindings) => null,
            default => $query->connection->prepareBindings($query->bindings),
        };

        $this->queries[] = [
            'query' => $sql,
            'type' => 'query',
            'bindings' => $bindings,
            'start' => $startTime,
            'time' => $time,
            'memory' => $this->lastMemoryUsage ? memory_get_usage(false) - $this->lastMemoryUsage : 0,
            'source' => $source,
            'connection' => $query->connection,
            'driver' => $query->connection->getConfig('driver'),
        ];

        if ($this->hasTimeDataCollector()) {
            $this->addTimeMeasure(Str::limit($sql, 100), $startTime, $endTime, [], 'Database Query');
        }
    }

    /**
     * Use a backtrace to search for the origins of the query.
     */
    protected function findSource(): array
    {
        $stack = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS | DEBUG_BACKTRACE_PROVIDE_OBJECT, app('config')->get('debugbar.debug_backtrace_limit', 50));

        $sources = [];

        foreach ($stack as $index => $trace) {
            $sources[] = $this->parseTrace($index, $trace);
        }

        return array_slice(array_filter($sources), 0, is_int($this->findSource) ? $this->findSource : 5);
    }

    /**
     * Parse a trace element from the backtrace stack.
     */
    protected function parseTrace(int $index, array $trace): object|bool
    {
        $frame = (object) [
            'index' => $index,
            'namespace' => null,
            'name' => null,
            'file' => null,
            'line' => $trace['line'] ?? '1',
        ];

        if (isset($trace['function']) && $trace['function'] === 'substituteBindings') {
            $frame->name = 'Route binding';

            return $frame;
        }

        if (
            isset($trace['class'])
            && isset($trace['file'])
            && !$this->fileIsInExcludedPath($trace['file'])
        ) {
            $frame->file = $trace['file'];

            if (isset($trace['object']) && is_a($trace['object'], '\Twig\Template')) {
                [$frame->file, $frame->line] = $this->getTwigInfo($trace);
            } elseif (str_contains($frame->file, storage_path())) {
                $hash = pathinfo($frame->file, PATHINFO_FILENAME);

                if ($frame->name = $this->findViewFromHash($hash)) {
                    $frame->file = $frame->name[1];
                    $frame->name = $frame->name[0];
                } else {
                    $frame->name = $hash;
                }

                $frame->namespace = 'view';

                return $frame;
            } elseif (str_contains($frame->file, 'Middleware')) {
                $frame->name = $this->findMiddlewareFromFile($frame->file);

                if ($frame->name) {
                    $frame->namespace = 'middleware';
                } else {
                    $frame->name = $this->normalizeFilePath($frame->file);
                }

                return $frame;
            }

            $frame->name = $this->normalizeFilePath($frame->file);

            return $frame;
        }

        return false;
    }

    /**
     * Check if the given file is to be excluded from analysis
     */
    protected function fileIsInExcludedPath(string $file): bool
    {
        $normalizedPath = str_replace('\\', '/', $file);

        foreach ($this->backtraceExcludePaths as $excludedPath) {
            if (str_contains($normalizedPath, $excludedPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Find the middleware alias from the file.
     */
    protected function findMiddlewareFromFile(string $file): ?string
    {
        $filename = pathinfo($file, PATHINFO_FILENAME);

        foreach ($this->middleware as $alias => $class) {
            if (is_string($class) && str_contains($class, $filename)) {
                return $alias;
            }
        }

        return null;
    }

    /**
     * Find the template name from the hash.
     */
    protected function findViewFromHash(string $hash): ?array
    {
        $finder = app('view')->getFinder();

        if (isset($this->reflection['viewfinderViews'])) {
            $property = $this->reflection['viewfinderViews'];
        } else {
            $reflection = new \ReflectionClass($finder);
            $property = $reflection->getProperty('views');
            $this->reflection['viewfinderViews'] = $property;
        }

        $xxh128Exists = in_array('xxh128', hash_algos(), true);

        foreach ($property->getValue($finder) as $name => $path) {
            if (($xxh128Exists && hash('xxh128', 'v2' . $path) === $hash) || sha1('v2' . $path) === $hash) {
                return [$name, $path];
            }
        }

        return null;
    }

    /**
     * Get the filename/line from a Twig template trace
     */
    protected function getTwigInfo(array $trace): array
    {
        $file = $trace['object']->getTemplateName();

        if (isset($trace['line'])) {
            foreach ($trace['object']->getDebugInfo() as $codeLine => $templateLine) {
                if ($codeLine <= $trace['line']) {
                    return [$file, $templateLine];
                }
            }
        }

        return [$file, -1];
    }

    /**
     * Adds a custom message to statements.
     */
    public function addMessage(string $message): void
    {
        $this->infoStatements++;

        $this->queries[] = [
            'sql' => $message,
            'type' => 'message',
            'start' => microtime(true),
        ];
    }

    /**
     * Collect a database transaction event.
     */
    public function collectTransactionEvent(string $event, mixed $connection): void
    {
        $this->transactionEventsCount++;
        $source = [];

        if ($this->findSource) {
            try {
                $source = $this->findSource();
            } catch (\Exception $e) {
            }
        }

        $this->queries[] = [
            'query' => $event,
            'type' => 'transaction',
            'bindings' => [],
            'start' => microtime(true),
            'time' => 0,
            'memory' => 0,
            'source' => $source,
            'connection' => $connection,
            'driver' => $connection->getConfig('driver'),
        ];
    }

    /**
     * Reset the queries.
     */
    public function reset(): void
    {
        $this->queries = [];
        $this->queryCount = 0;
        $this->infoStatements = 0 ;
        $this->transactionEventsCount = 0;
        $this->reflection = [];
    }

    /**
     * {@inheritDoc}
     */
    public function collect(): array
    {
        $totalTime = 0;
        $totalMemory = 0;
        $queries = $this->queries;

        $statements = [];
        foreach ($queries as $query) {
            if ($query['type'] === 'message') {
                $statements[] = $query;
                continue;
            }

            $source = reset($query['source']);
            $normalizedPath = is_object($source) ? $this->normalizeFilePath($source->file ?: '') : '';
            if ($query['type'] !== 'transaction' && Str::startsWith($normalizedPath, $this->excludePaths)) {
                continue;
            }

            $totalTime += $query['time'];
            $totalMemory += $query['memory'];

            $connectionName = $query['connection']->getDatabaseName();
            if (str_ends_with($connectionName, '.sqlite')) {
                $connectionName = $this->normalizeFilePath($connectionName);
            }

            $canExplainQuery = match (true) {
                in_array($query['driver'], ['mariadb', 'mysql', 'pgsql'], true) => $query['bindings'] !== null && preg_match('/^\s*(' . implode('|', $this->explainTypes) . ') /i', $query['query']),
                default => false,
            };

            $statements[] = [
                'sql' => $this->getSqlQueryToDisplay($query),
                'type' => $query['type'],
                'params' => $query['bindings'] ?? [],
                'backtrace' => array_values($query['source']),
                'start' => $query['start'] ?? null,
                'duration' => $query['time'],
                'duration_str' => ($query['type'] === 'transaction') ? '' : $this->getDataFormatter()->formatDuration($query['time']),
                'slow' => $this->slowThreshold && $this->slowThreshold <= $query['time'],
                'memory' => $query['memory'],
                'memory_str' => $query['memory'] ? $this->getDataFormatter()->formatBytes($query['memory']) : null,
                'filename' => $source ? $this->getQueryFormatter()->formatSource($source, true) : null,
                'source' => $source,
                'xdebug_link' => is_object($source) ? $this->getXdebugLink($source->file ?: '', $source->line) : null,
                'connection' => $connectionName,
                'explain' => $this->explainQuery && $canExplainQuery ? [
                    'url' => route('debugbar.queries.explain'),
                    'driver' => $query['driver'],
                    'connection' => $query['connection']->getName(),
                    'query' => $query['query'],
                    'hash' => (new Explain())->hash($query['connection']->getName(), $query['query'], $query['bindings']),
                ] : null,
            ];
        }

        if ($this->durationBackground) {
            if ($totalTime > 0) {
                // For showing background measure on Queries tab
                $start_percent = 0;

                foreach ($statements as $i => $statement) {
                    if (!isset($statement['duration'])) {
                        continue;
                    }

                    $width_percent = $statement['duration'] / $totalTime * 100;

                    $statements[$i] = array_merge($statement, [
                        'start_percent' => round($start_percent, 3),
                        'width_percent' => round($width_percent, 3),
                    ]);

                    $start_percent += $width_percent;
                }
            }
        }

        if ($this->softLimit && $this->hardLimit && ($this->queryCount > $this->softLimit && $this->queryCount > $this->hardLimit)) {
            array_unshift($statements, [
                'sql' => '# Query soft and hard limit for Debugbar are reached. Only the first ' . $this->softLimit . ' queries show details. Queries after the first ' . $this->hardLimit . ' are ignored. Limits can be raised in the config (debugbar.options.db.soft/hard_limit).',
                'type' => 'info',
            ]);
            $statements[] = [
                'sql' => '... ' . ($this->queryCount - $this->hardLimit) . ' additional queries are executed but now shown because of Debugbar query limits. Limits can be raised in the config (debugbar.options.db.soft/hard_limit)',
                'type' => 'info',
            ];
            $this->infoStatements += 2;
        } elseif ($this->hardLimit && $this->queryCount > $this->hardLimit) {
            array_unshift($statements, [
                'sql' => '# Query hard limit for Debugbar is reached after ' . $this->hardLimit . ' queries, additional ' . ($this->queryCount - $this->hardLimit) . ' queries are not shown.. Limits can be raised in the config (debugbar.options.db.hard_limit)',
                'type' => 'info',
            ]);
            $statements[] = [
                'sql' => '... ' . ($this->queryCount - $this->hardLimit) . ' additional queries are executed but now shown because of Debugbar query limits. Limits can be raised in the config (debugbar.options.db.hard_limit)',
                'type' => 'info',
            ];
            $this->infoStatements += 2;
        } elseif ($this->softLimit && $this->queryCount > $this->softLimit) {
            array_unshift($statements, [
                'sql' => '# Query soft limit for Debugbar is reached after ' . $this->softLimit . ' queries, additional ' . ($this->queryCount - $this->softLimit) . ' queries only show the query. Limits can be raised in the config (debugbar.options.db.soft_limit)',
                'type' => 'info',
            ]);
            $this->infoStatements++;
        }

        $visibleStatements = count($statements) - $this->infoStatements;

        $data = [
            'count' => $visibleStatements,
            'nb_statements' => $this->queryCount,
            'nb_visible_statements' => $visibleStatements,
            'nb_excluded_statements' => $this->queryCount + $this->transactionEventsCount - $visibleStatements,
            'nb_failed_statements' => 0,
            'accumulated_duration' => $totalTime,
            'accumulated_duration_str' => $this->getDataFormatter()->formatDuration($totalTime),
            'memory_usage' => $totalMemory,
            'memory_usage_str' => $totalMemory ? $this->getDataFormatter()->formatBytes($totalMemory) : null,
            'statements' => $statements,
        ];
        return $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getName(): string
    {
        return 'queries';
    }

    /**
     * {@inheritDoc}
     */
    public function getWidgets(): array
    {
        return [
            "queries" => [
                "icon" => "database",
                "widget" => "PhpDebugBar.Widgets.LaravelQueriesWidget",
                "map" => "queries",
                "default" => "[]",
            ],
            "queries:badge" => [
                "map" => "queries.nb_statements",
                "default" => 0,
            ],
        ];
    }

    protected function getSqlQueryToDisplay(array $query): string
    {
        $sql = $query['query'];
        $grammar = $query['connection']->getQueryGrammar();
        if ($query['type'] === 'query' && $grammar instanceof Grammar) {
            try {
                $sql = $grammar->substituteBindingsIntoRawSql($sql, $query['bindings'] ?? []);
                return $this->getQueryFormatter()->formatSql($sql);
            } catch (\Throwable $e) {
                // Continue using the old substitute
            }
        }

        if ($query['type'] === 'query' && $this->renderSqlWithParams) {
            $pdo = null;
            try {
                $pdo = $query['connection']->getPdo();
            } catch (\Throwable) {
                // ignore error for non-pdo laravel drivers
            }

            $sql = $this->getQueryFormatter()->formatSqlWithBindings($sql, $query['bindings'] ?? [], $pdo);
        }

        return $this->getQueryFormatter()->formatSql($sql);
    }

    public function getAssets(): array
    {
        return [
            'js' => [
                'widgets/sqlqueries/widget.js',
                __DIR__ . '/../../resources/queries/widget.js',
            ],
            'css' => 'widgets/sqlqueries/widget.css',
        ];
    }
}
