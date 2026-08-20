<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Console\Concerns;

trait AnalyzesQueries
{
    /**
     * Group statement indexes by sql+params+connection, matching the front-end logic.
     * These are exact repeats: the same query, run more than once, with the same values.
     *
     * @return array<string, list<int>>
     */
    protected function duplicateGroups(array $statements): array
    {
        $groups = [];
        foreach ($statements as $i => $stmt) {
            if (($stmt['type'] ?? 'query') !== 'query') {
                continue;
            }
            $groups[$this->exactKey($stmt)][] = $i;
        }

        return array_filter($groups, fn(array $indices): bool => count($indices) > 1);
    }

    /**
     * Group statement indexes by the *shape* of the query, ignoring literal values.
     * A classic N+1 runs the same query with a different id per record, so it never
     * shows up as an exact duplicate. Groups whose members are all identical are
     * excluded, because those are already reported as duplicates.
     *
     * @return array<string, list<int>>
     */
    protected function nPlusOneGroups(array $statements): array
    {
        $groups = [];
        foreach ($statements as $i => $stmt) {
            if (($stmt['type'] ?? 'query') !== 'query') {
                continue;
            }
            $key = $this->normalizeSql($stmt['sql'] ?? '') . '@' . ($stmt['connection'] ?? '');
            $groups[$key][] = $i;
        }

        return array_filter($groups, function (array $indices) use ($statements): bool {
            if (count($indices) < 2) {
                return false;
            }

            $variants = [];
            foreach ($indices as $i) {
                $variants[$this->exactKey($statements[$i])] = true;
            }

            return count($variants) > 1;
        });
    }

    /**
     * Failed statements keyed by their index. `nb_failed_statements` is not
     * populated by the collector, so the statements are the source of truth.
     *
     * @return array<int, array>
     */
    protected function failedStatements(array $statements): array
    {
        return array_filter(
            $statements,
            fn(array $stmt): bool => ($stmt['is_success'] ?? true) === false || isset($stmt['error_message'])
        );
    }

    /**
     * Strip literal values so two runs of the same query with different values
     * collapse onto one key. The collector renders bindings into the SQL, so
     * comparing the raw string is not enough.
     */
    protected function normalizeSql(string $sql): string
    {
        // Quoted string literals, including '' escaped quotes
        $sql = preg_replace("/'(?:[^']|'')*'/", '?', $sql) ?? $sql;

        // Standalone numeric literals, leaving identifiers like `posts2` alone
        $sql = preg_replace('/(?<![\w.])\d+(?:\.\d+)?(?![\w.])/', '?', $sql) ?? $sql;

        // `in (?, ?, ?)` and `in (?)` are the same shape
        $sql = preg_replace('/\bin\s*\(\s*\?(?:\s*,\s*\?)*\s*\)/i', 'in (?)', $sql) ?? $sql;

        return (string) preg_replace('/\s+/', ' ', $sql);
    }

    private function exactKey(array $stmt): string
    {
        $key = $stmt['sql'] ?? '';
        if (isset($stmt['params']) && count($stmt['params']) > 0) {
            $key .= json_encode($stmt['params']);
        }
        if (isset($stmt['connection'])) {
            $key .= '@' . $stmt['connection'];
        }

        return $key;
    }
}
