## Laravel Debugbar

Laravel Debugbar integrates PHP Debug Bar with Laravel. It collects data from your application during each request (queries, views, routes, mail, etc.) and stores it for review.

### Artisan Commands

- `debugbar:find` - Search stored debugbar requests with filters. Useful for finding specific requests to inspect.
- `debugbar:get {id}` - View details of a specific debugbar request by its ID.
- `debugbar:queries {id}` - Inspect queries for a specific request, with duplicate detection, EXPLAIN, and result output.
- `debugbar:clear` - Clear all stored debugbar data.

### Finding Requests

Use `debugbar:find` to search through stored requests:

@verbatim
<code-snippet name="Find recent requests" lang="bash">
php artisan debugbar:find
</code-snippet>
@endverbatim

@verbatim
<code-snippet name="Find requests with filters" lang="bash">
# Filter by HTTP method
php artisan debugbar:find --method=POST

# Filter by URI pattern (fnmatch format)
php artisan debugbar:find --uri="/api/*"

# Filter by IP address
php artisan debugbar:find --ip=127.0.0.1

# Combine filters with pagination
php artisan debugbar:find --method=GET --uri="/admin/*" --max=50 --offset=0
</code-snippet>
@endverbatim

### Inspecting a Request

After finding a request ID with `debugbar:find`, inspect it with `debugbar:get` to get a summary,
and add --collector=name to get the full details for that collector.

@verbatim
<code-snippet name="Get request details" lang="bash">
# Show summary of all collectors for the latest request
php artisan debugbar:get latest

# Show summary by specific ID
php artisan debugbar:get {id}

# View a specific collector (e.g. queries, views, route, mail)
php artisan debugbar:get latest --collector=queries

# Output raw JSON data
php artisan debugbar:get latest
</code-snippet>
@endverbatim

### Inspecting Queries

Use `debugbar:queries` to view queries with duplicate detection, run EXPLAIN, or re-execute a query:

@verbatim
<code-snippet name="Inspect queries" lang="bash">
# Show all queries for the latest request, with duplicate counts
php artisan debugbar:queries latest

# Show all queries for a specific request, with duplicate counts
php artisan debugbar:queries {id}

# Show details for a specific statement (backtrace, params)
php artisan debugbar:queries {id} --statement=2

# Run EXPLAIN on a specific query
php artisan debugbar:queries {id} --statement=2 --explain

# Re-execute a SELECT query and show results
php artisan debugbar:queries {id} --statement=2 --result
</code-snippet>
@endverbatim

### Configuration

- Debugbar is enabled by default when APP_DEBUG=true. It should be disabled in production.
- Collectors can be enabled/disabled individually in `config/debugbar.php` under the `collectors` key.
