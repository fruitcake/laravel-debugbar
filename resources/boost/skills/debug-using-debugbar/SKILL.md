---
name: debug-using-debugbar
description: >
  Inspect what a Laravel request actually did — queries, exceptions, timing, cache, auth, views — by reading
  the profiling data Laravel Debugbar already captured, via Artisan commands. Use when investigating a bug,
  a slow page or endpoint, an N+1 or duplicate query problem, a failing or slow SQL statement, a 500 or
  unexpected status code, or when asked to optimize a request. Also covers queued jobs and Artisan commands.
  Applies even when the user does not mention "debugbar" or "profiling".
compatibility: Requires Laravel with fruitcake/laravel-debugbar installed, debug mode enabled, and `debugbar.storage.enabled` set.
---

## Debugging and optimizing workflow

1. Find the relevant request:
   ```bash
   php artisan debugbar:find --issues --max=50
   ```
2. Inspect the request summary to see which collectors have data:
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

If the storage is empty, there is nothing to debug yet — ask the user to exercise the page or endpoint first,
or trigger it yourself, then run `debugbar:find` again.

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

Queued jobs and Artisan commands are stored too, with `method` set to `JOB` or `CLI`:

```bash
php artisan debugbar:find --method=JOB      # queued jobs
php artisan debugbar:find --method=CLI      # artisan commands
```

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
- **N+1 / ORM** → `queries`, `models` · **View overhead** → `views` · **External calls** → `http_client`
- **Log output** → `log` (Laravel log events), `messages` (`debug()` calls), `logs` (log file tail)

If the collector name is wrong, the command lists the collectors that actually have data for that request.

## Analyzing queries

```bash
# Overview with duplicate detection, slow flags and failed statements
php artisan debugbar:queries {id}

# Backtrace and params for a specific statement
php artisan debugbar:queries {id} --statement=N

# EXPLAIN plan or re-execute a SELECT
php artisan debugbar:queries {id} --statement=N --explain
php artisan debugbar:queries {id} --statement=N --result
```

The `Flags` column marks `SLOW` and `FAILED` statements; failed statements are listed again below the table with their driver error.

Two separate repetition reports follow the table, and they mean different things:

- **Duplicate queries** — identical SQL *and* identical bindings. Usually a query that should have been cached or hoisted out of a loop.
- **Repeated query shapes with varying bindings** — the same query with a different value each time. This is the classic N+1: an unloaded relation fetched per record. Fix it with eager loading (`with()`). Detection strips literals from the SQL, so `where user_id = 1` and `where user_id = 2` count as one shape.

Use `--statement=N` on any index from those groups to get the backtrace and find the origin.

## JSON output

All three read commands accept `--json`, which is easier to parse than the tables and preserves exact numbers:

```bash
php artisan debugbar:find --issues --json     # array of requests, each with an `issues` list
php artisan debugbar:queries {id} --json      # statements plus `duplicate_groups` and `n_plus_one_groups`
php artisan debugbar:get {id} --json          # raw collector data (`--raw` is the same thing)
```

## Gotchas

- Always start with `debugbar:find --issues` rather than `debugbar:find` — the issue flags surface the most actionable requests immediately.
- The `{id}` is the request ID from the `debugbar:find` output, or use `latest` to inspect the most recent request.
- Collector availability depends on the app's debugbar config — the summary from `debugbar:get` shows which collectors have data.
- The `Dup` column only counts *exact* duplicates (same bindings). For N+1 read the "repeated query shapes" section instead — that is where a per-record lazy load shows up.
- `--explain` and `--result` only work on SELECT queries, and require `--statement=N`. They re-execute against the current database, so results may differ from the original request.
- Very large requests are truncated by the debugbar query limits (`debugbar.options.db.soft_limit` / `hard_limit`); an `info` statement in the output says so when it happens.
- `debugbar:clear` removes all stored data — use it to reset between debugging sessions, not mid-investigation.
