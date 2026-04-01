---
name: debug-using-debugbar
description: >
  Use this skill to optimize requests or debug Laravel application issues — slow pages, N+1 queries, exceptions,
  failed requests, or unexpected behavior — by inspecting data captured by Laravel Debugbar via
  Artisan CLI commands. Use when the user asks to investigate a bug, diagnose a slow request,
  find duplicate queries, check what happened on a previous request, or optimize database
  performance, even if they don't explicitly mention "debugbar" or "profiling."
compatibility: Requires Laravel with fruitcake/laravel-debugbar installed and debug mode enabled.
---

## Debugging and optimizing workflow

1. Find the relevant request:
   ```bash
   php artisan debugbar:find --issues --max=50
   ```
2. Inspect the request summary to identify which collectors have data:
   ```bash
   php artisan debugbar:get {id}
   ```
3. Drill into the relevant collector based on the issue type:
   ```bash
   php artisan debugbar:get {id} --collector=exceptions
   ```
4. For query issues, use dedicated query analysis:
   ```bash
   php artisan debugbar:queries {id}
   ```
5. Trace the problem to source code using backtraces, then fix and re-test.

## Finding requests

```bash
# List recent requests (shows summary with status, duration, memory, query count)
php artisan debugbar:find

# Filter by URI pattern (fnmatch) and/or HTTP method
php artisan debugbar:find --uri="/api/*" --method=POST

# Only show requests with issues (exceptions, slow queries, duplicates, errors)
php artisan debugbar:find --issues --max=50

# Customize issue thresholds (defaults: --min-queries=50, --min-duration=1000, --min-duplicates=2)
php artisan debugbar:find --issues --min-queries=10 --min-duration=500

# Threshold options also work standalone, filtering on just that criteria
php artisan debugbar:find --min-queries=20
```

`--issues` flags: exceptions, non-2xx status, high query count, slow queries, duplicate query groups, slow request duration, and failed queries. Issue filtering applies on top of the fetched result set — increase `--max` to scan further back.

## Inspecting a request

```bash
# Summary of all collectors (available collectors depend on config)
php artisan debugbar:get latest
php artisan debugbar:get {id}

# Full data for a specific collector
php artisan debugbar:get {id} --collector=exceptions
```

Pick the collector by issue type:
- **Error/500** → `exceptions` · **Slow page** → `queries`, `time` · **Auth** → `auth`, `gate` · **Cache** → `cache`

## Analyzing queries

```bash
# Overview with duplicate detection and slow query flags
php artisan debugbar:queries {id}

# Backtrace and params for a specific statement
php artisan debugbar:queries {id} --statement=N

# EXPLAIN plan or re-execute a SELECT
php artisan debugbar:queries {id} --statement=N --explain
php artisan debugbar:queries {id} --statement=N --result
```

Duplicate queries are a strong N+1 signal. Use `--statement=N` to get the backtrace and find the origin.

## Gotchas

- Always start with `debugbar:find --issues` rather than `debugbar:find` — the issue flags surface the most actionable requests immediately.
- The `{id}` is the request ID from the `debugbar:find` output, or use `latest` to inspect the most recent request.
- Collector availability depends on the app's debugbar config — the summary from `debugbar:get` shows which collectors have data.
- `--explain` and `--result` only work on SELECT queries. They re-execute against the current database, so results may differ from the original request.
- `debugbar:clear` removes all stored data — use it to reset between debugging sessions, not mid-investigation.
