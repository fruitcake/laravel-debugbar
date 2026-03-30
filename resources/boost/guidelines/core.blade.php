## Laravel Debugbar

Laravel Debugbar stores data from each request (queries, exceptions, views, routes, mail, etc.) for review via Artisan commands.

### Finding Requests

@verbatim
<code-snippet name="Find requests" lang="bash">
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
</code-snippet>
@endverbatim

`--issues` flags: exceptions, non-2xx status, high query count, slow queries, duplicate query groups, slow request duration, and failed queries. Issue filtering applies on top of the fetched result set — increase `--max` to scan further back.

### Inspecting a Request

@verbatim
<code-snippet name="Inspect request" lang="bash">
# Summary of all collectors (available collectors depend on config)
php artisan debugbar:get latest
php artisan debugbar:get {id}

# Full data for a specific collector
php artisan debugbar:get {id} --collector=exceptions
</code-snippet>
@endverbatim

Use the collector name from the summary table. Common ones by issue type:
- **Error/500** → `exceptions` · **Slow page** → `queries`, `time` · **Auth** → `auth`, `gate` · **Cache** → `cache`

### Analyzing Queries

@verbatim
<code-snippet name="Query analysis" lang="bash">
# Overview with duplicate detection and slow query flags
php artisan debugbar:queries {id}

# Backtrace and params for a specific statement
php artisan debugbar:queries {id} --statement=N

# EXPLAIN plan or re-execute a SELECT
php artisan debugbar:queries {id} --statement=N --explain
php artisan debugbar:queries {id} --statement=N --result
</code-snippet>
@endverbatim

Duplicate queries are a strong N+1 signal. Use `--statement=N` to get the backtrace and find the origin.

### Other Commands

- `debugbar:clear` — Clear all stored debugbar data.
