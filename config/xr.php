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
