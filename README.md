# xrDebug Laravel client

<a href="https://xrdebug.com"><img alt="xrDebug" src="xr.svg" width="40%"></a>

[![Build](https://img.shields.io/github/actions/workflow/status/xrdebug/laravel/test.yml?branch=0.1&style=flat-square)](https://github.com/xrdebug/laravel/actions)
![Code size](https://img.shields.io/github/languages/code-size/xrdebug/laravel?style=flat-square)
[![Apache-2.0](https://img.shields.io/github/license/xrdebug/laravel?style=flat-square)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%209-blueviolet?style=flat-square)](https://phpstan.org/)
[![Mutation testing badge](https://img.shields.io/endpoint?style=flat-square&url=https%3A%2F%2Fbadge-api.stryker-mutator.io%2Fgithub.com%2Fxrdebug%2Flaravel%2F0.1)](https://dashboard.stryker-mutator.io/reports/github.com/xrdebug/laravel/0.1)

[![Quality Gate Status](https://sonarcloud.io/api/project_badges/measure?project=xrdebug_laravel&metric=alert_status)](https://sonarcloud.io/dashboard?id=xrdebug_laravel)
[![Maintainability Rating](https://sonarcloud.io/api/project_badges/measure?project=xrdebug_laravel&metric=sqale_rating)](https://sonarcloud.io/dashboard?id=xrdebug_laravel)
[![Reliability Rating](https://sonarcloud.io/api/project_badges/measure?project=xrdebug_laravel&metric=reliability_rating)](https://sonarcloud.io/dashboard?id=xrdebug_laravel)
[![Security Rating](https://sonarcloud.io/api/project_badges/measure?project=xrdebug_laravel&metric=security_rating)](https://sonarcloud.io/dashboard?id=xrdebug_laravel)
[![Coverage](https://sonarcloud.io/api/project_badges/measure?project=xrdebug_laravel&metric=coverage)](https://sonarcloud.io/dashboard?id=xrdebug_laravel)
[![Technical Debt](https://sonarcloud.io/api/project_badges/measure?project=xrdebug_laravel&metric=sqale_index)](https://sonarcloud.io/dashboard?id=xrdebug_laravel)
[![CodeFactor](https://www.codefactor.io/repository/github/xrdebug/laravel/badge)](https://www.codefactor.io/repository/github/xrdebug/laravel)

## Summary

Laravel client library for [xrDebug](https://xrdebug.com/). This library provides a set of functions to dump variables, send raw messages, and interact with the inspector.

## Quickstart

1. Install the package in your Laravel app:

```sh
composer require --dev xrdebug/laravel
```

2. (Optional) Publishing the config file:

```sh
php artisan xr:publish-config
```

This creates `xr.php` at the project root so you can tune host, port, and watcher toggles.

Add `--docker` or `--homestead` flags to set the default host for those environments:

```sh
php artisan xr:publish-config --docker
php artisan xr:publish-config --homestead
```

3. Use xrDebug helpers anywhere in your Laravel code:

```php
xr('Hello from Laravel');
xr(userId: auth()->id(), t: 'Auth');
```

Laravel package auto-discovery registers the service provider automatically.

## Configuration

Configuration can be defined in `xr.php` at project root and in `config/xr.php`.
If both files exist, `config/xr.php` takes precedence.

```php
<?php

return [
    /**
     * This setting controls whether data should be sent to xrDebug.
     */
    'enabled' => env('XR_ENABLED', env('APP_ENV', 'production') !== 'production'),

    /**
     * The host used to communicate with the xrDebug server.
     *
     * When using Docker on Mac or Windows, you can replace localhost with 'host.docker.internal'
     * When using Docker on Linux, you can replace localhost with '172.17.0.1'
     * When using Homestead with the VirtualBox provider, you can replace localhost with '10.0.2.2'
     * When using Homestead with the Parallels provider, you can replace localhost with '10.211.55.2'
     */
    'host' => env('XR_HOST', 'localhost'),

    /**
     * The port number used to communicate with the xrDebug server.
     */
    'port' => env('XR_PORT', 27420),

    /**
     * When enabled, communication with the xrDebug server will use HTTPS.
     */
    'https' => env('XR_HTTPS', false),

    /**
     * The private key used to sign messages sent to the xrDebug server.
     */
    'key' => env('XR_KEY', ''),

    /**
     * Absolute base path for your sites or projects on your local
     * computer where your IDE or code editor is running on.
     */
    'local_path' => env('XR_LOCAL_PATH', ''),

    /**
     * Absolute base path for your sites or projects in Homestead,
     * Vagrant, Docker, or another remote development server.
     */
    'remote_path' => env('XR_REMOTE_PATH', ''),

    /**
     * When enabled, all cache events will automatically be sent to xrDebug.
     */
    'show_cache' => env('XR_SHOW_CACHE', false),

    /**
     * When enabled, all deprecation notices will automatically be sent to xrDebug.
     */
    'show_deprecated_notices' => env('XR_SHOW_DEPRECATED_NOTICES', false),

    /**
     * When enabled, all DELETE queries will automatically be sent to xrDebug.
     */
    'show_delete_queries' => env('XR_SHOW_DELETE_QUERIES', false),

    /**
     * When enabled, all things passed to `dump` or `dd` will be sent to xrDebug as well.
     */
    'show_dumps' => env('XR_SHOW_DUMPS', true),

    /**
     * When enabled, all duplicate queries will automatically be sent to xrDebug.
     */
    'show_duplicate_queries' => env('XR_SHOW_DUPLICATE_QUERIES', false),

    /**
     * When enabled, all events will automatically be sent to xrDebug.
     */
    'show_events' => env('XR_SHOW_EVENTS', false),

    /**
     * When enabled, all exceptions will automatically be sent to xrDebug.
     */
    'show_exceptions' => env('XR_SHOW_EXCEPTIONS', true),

    /**
     * When enabled, all HTTP client requests made by this app will automatically be sent to xrDebug.
     */
    'show_http_client_requests' => env('XR_SHOW_HTTP_CLIENT_REQUESTS', false),

    /**
     * When enabled, all INSERT queries will automatically be sent to xrDebug.
     */
    'show_insert_queries' => env('XR_SHOW_INSERT_QUERIES', false),

    /**
     * When enabled, all job events will automatically be sent to xrDebug.
     */
    'show_jobs' => env('XR_SHOW_JOBS', false),

    /**
     * When enabled, all things logged to the application log will be sent to xrDebug as well.
     */
    'show_logs' => env('XR_SHOW_LOGS', true),

    /**
     * When enabled, all mails will automatically be sent to xrDebug.
     */
    'show_mails' => env('XR_SHOW_MAILS', true),

    /**
     * When enabled, all queries will automatically be sent to xrDebug.
     */
    'show_queries' => env('XR_SHOW_QUERIES', false),

    /**
     * When enabled, all requests made to this app will automatically be sent to xrDebug.
     */
    'show_requests' => env('XR_SHOW_REQUESTS', false),

    /**
     * When enabled, all SELECT queries will automatically be sent to xrDebug.
     */
    'show_select_queries' => env('XR_SHOW_SELECT_QUERIES', false),

    /**
     * When enabled, slow queries will automatically be sent to xrDebug.
     */
    'show_slow_queries' => env('XR_SHOW_SLOW_QUERIES', false),

    /**
     * Queries that take longer than this number of milliseconds will be regarded as slow.
     */
    'slow_query_threshold_ms' => env('XR_SLOW_QUERY_THRESHOLD_MS', 500),

    /**
     * When enabled, all UPDATE queries will automatically be sent to xrDebug.
     */
    'show_update_queries' => env('XR_SHOW_UPDATE_QUERIES', false),

    /**
     * When enabled, all views that are rendered will automatically be sent to xrDebug.
     */
    'show_views' => env('XR_SHOW_VIEWS', false),
];
```

## Usage

### Send debug data manually

Use helpers from [xrdebug/php](https://github.com/xrdebug/php#debug-helpers) directly in controllers, jobs, commands, listeners, etc.

```php
// Variable dump
xr($request->all(), t: 'Incoming request');

// Raw HTML body
xrr('<h3>Checkout started</h3><p>step: payment</p>', t: 'Checkout');

// Inspector actions
xri()->memory();
xri()->pause();
```

### Use the xrLaravel inspector helper

Use `xrLaravel()` when you want to toggle Laravel event/query/request watchers inline.

```php
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

// Enable watchers globally for the current runtime.
xrLaravel()
    ->showMails()
    ->showEvents()
    ->showQueries();

Mail::raw('Hello', fn ($m) => $m->to('dev@example.com'));
Cache::put('session-key', 'value', 60);

// Turn watchers off when you are done.
xrLaravel()
    ->stopShowingMails()
    ->stopShowingEvents()
    ->stopShowingQueries();

// Scope watcher activation to a callable.
xrLaravel()->showHttpClientRequests(
    function (): void {
        Http::get('https://example.com/health');
    }
);
```

When a `show*` method receives a callable, the watcher is enabled for that callable execution. If the watcher was already enabled before the call, it remains enabled.

## Reference

The package can automatically send Laravel runtime events to xrDebug using config flags in `xr.php` or `config/xr.php`. All methods are available on `xrLaravel()` and return the inspector instance for chaining.

### show_logs

Env: `XR_SHOW_LOGS` — Default: `true`

```php
xrLaravel()->showLogs();
xrLaravel()->stopShowingLogs();
```

### show_exceptions

Env: `XR_SHOW_EXCEPTIONS` — Default: `true`

```php
xrLaravel()->showExceptions();
xrLaravel()->stopShowingExceptions();
```

### show_mails

Env: `XR_SHOW_MAILS` — Default: `true`

```php
xrLaravel()->showMails();
xrLaravel()->showMails(function () { /* scoped */ });
xrLaravel()->stopShowingMails();
```

### show_dumps

Env: `XR_SHOW_DUMPS` — Default: `true`

### show_requests

Env: `XR_SHOW_REQUESTS` — Default: `false`

```php
xrLaravel()->showRequests();
xrLaravel()->showRequests(function () { /* scoped */ });
xrLaravel()->stopShowingRequests();
```

### show_queries

Env: `XR_SHOW_QUERIES` — Default: `false`

```php
xrLaravel()->showQueries();
xrLaravel()->showQueries(function () { /* scoped */ });
xrLaravel()->stopShowingQueries();
```

### show_select_queries

Env: `XR_SHOW_SELECT_QUERIES` — Default: `false`

```php
xrLaravel()->showSelectQueries();
xrLaravel()->showSelectQueries(function () { /* scoped */ });
xrLaravel()->stopShowingSelectQueries();
```

### show_insert_queries

Env: `XR_SHOW_INSERT_QUERIES` — Default: `false`

```php
xrLaravel()->showInsertQueries();
xrLaravel()->showInsertQueries(function () { /* scoped */ });
xrLaravel()->stopShowingInsertQueries();
```

### show_update_queries

Env: `XR_SHOW_UPDATE_QUERIES` — Default: `false`

```php
xrLaravel()->showUpdateQueries();
xrLaravel()->showUpdateQueries(function () { /* scoped */ });
xrLaravel()->stopShowingUpdateQueries();
```

### show_delete_queries

Env: `XR_SHOW_DELETE_QUERIES` — Default: `false`

```php
xrLaravel()->showDeleteQueries();
xrLaravel()->showDeleteQueries(function () { /* scoped */ });
xrLaravel()->stopShowingDeleteQueries();
```

### show_duplicate_queries

Env: `XR_SHOW_DUPLICATE_QUERIES` — Default: `false`

```php
xrLaravel()->showDuplicateQueries();
xrLaravel()->showDuplicateQueries(function () { /* scoped */ });
xrLaravel()->stopShowingDuplicateQueries();
```

### show_slow_queries

Env: `XR_SHOW_SLOW_QUERIES` — Default: `false`

```php
xrLaravel()->showSlowQueries();
xrLaravel()->showSlowQueries(ms: 500, callable: function () { /* scoped */ });
xrLaravel()->stopShowingSlowQueries();
```

### show_cache

Env: `XR_SHOW_CACHE` — Default: `false`

```php
xrLaravel()->showCache();
xrLaravel()->showCache(function () { /* scoped */ });
xrLaravel()->stopShowingCache();
```

### show_events

Env: `XR_SHOW_EVENTS` — Default: `false`

```php
xrLaravel()->showEvents();
xrLaravel()->showEvents(function () { /* scoped */ });
xrLaravel()->stopShowingEvents();
```

### show_jobs

Env: `XR_SHOW_JOBS` — Default: `false`

```php
xrLaravel()->showJobs();
xrLaravel()->showJobs(function () { /* scoped */ });
xrLaravel()->stopShowingJobs();
```

### show_http_client_requests

Env: `XR_SHOW_HTTP_CLIENT_REQUESTS` — Default: `false`

```php
xrLaravel()->showHttpClientRequests();
xrLaravel()->showHttpClientRequests(function () { /* scoped */ });
xrLaravel()->stopShowingHttpClientRequests();
```

### show_deprecated_notices

Env: `XR_SHOW_DEPRECATED_NOTICES` — Default: `false`

### show_views

Env: `XR_SHOW_VIEWS` — Default: `false`

```php
xrLaravel()->showViews();
xrLaravel()->showViews(function () { /* scoped */ });
xrLaravel()->stopShowingViews();
```

Additional methods not tied to a config flag:

```php
xrLaravel()->countQueries(callable $callable);
xrLaravel()->showConditionalQueries(Closure $condition, $callable = null, $name = 'default');
xrLaravel()->stopShowingConditionalQueries($name = 'default');
```

Examples by concern:

```php
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Support\Str;

// Queries executed in this closure are sent.
xrLaravel()->showQueries(
    function (): void {
        DB::table('users')->where('active', true)->get();
    }
);

// Sends a query statistics summary (count and total time) for this closure.
xrLaravel()->countQueries(
    function (): void {
        DB::table('users')->where('active', true)->get();
        DB::table('users')->where('verified', true)->count();
    }
);

// Only queries slower than 250ms are sent.
xrLaravel()->showSlowQueries(250);

// Only duplicate queries are sent.
xrLaravel()->showDuplicateQueries();

// Only queries matching your predicate are sent.
xrLaravel()->showConditionalQueries(
    function (QueryExecuted $query): bool {
        return Str::contains($query->sql, 'users');
    },
    name: 'users-only'
);

// Query type filters.
xrLaravel()->showSelectQueries();
xrLaravel()->showInsertQueries();
xrLaravel()->showUpdateQueries();
xrLaravel()->showDeleteQueries();

// Stop a named conditional watcher.
xrLaravel()->stopShowingConditionalQueries('users-only');
```

```php
// Requests received by Laravel.
xrLaravel()->showRequests();

// Outgoing Laravel HTTP client requests.
xrLaravel()->showHttpClientRequests();

// View rendering events.
xrLaravel()->showViews();

// Application lifecycle/events/jobs/cache/mail/exception instrumentation.
xrLaravel()->showEvents();
xrLaravel()->showJobs();
xrLaravel()->showCache();
xrLaravel()->showMails();
xrLaravel()->showExceptions();
```

### Use dump and dd

When `show_dumps` is enabled, Laravel `dump()`/`dd()` output is also forwarded to xrDebug through the VarDumper handler.

```php
dump($user);
// dd($user);
```

## License

Copyright [Rodolfo Berrios A.](https://rodolfoberrios.com/)

xrDebug is licensed under the Apache License, Version 2.0. See [LICENSE](LICENSE) for the full license text.

Unless required by applicable law or agreed to in writing, software distributed under the License is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License for the specific language governing permissions and limitations under the License.
