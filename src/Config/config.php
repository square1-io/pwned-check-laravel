<?php

return [
    /*
     * Required
     * If you don't have a user agent set up then Have I been pwned will reject your request
     */
    'user_agent' => env('PWNED_CHECK_USER_AGENT', 'Square1 Pwned PHP package for Laravel - '.env('APP_NAME')),

    /**
     * Force a timeout on the HIBP connection so any slowdown there won't impact your app. 0 for 'no timeout'
     */
    'connection_timeout' => env('PWNED_CHECK_CONNECTION_TIMEOUT', 0),
    'remote_processing_timeout' => env('PWNED_CHECK_REMOTE_PROCESSING_TIMEOUT', 0),

    /**
     * When the connection to HIBP times out, should the request be considered invalidated? By default, it
     * will be, but change this setting if you'd rather HIBP is used effectively as an advisory service, not
     * forcing a failure if the service is unreachable
     */
    'fail_on_timeout' => env('PWNED_CHECK_FAIL_ON_TIMEOUT', true),

    /**
     * Default number of minutes to cache API responses for a password
     */
    'cache_default_ttl' => env('PWNED_CHECK_DEFAULT_API_CACHE_TTL', 60 * 24),

    /**
     * Endpoint to use on HIBP for password range queries
     */
    'range_endpoint' => env('PWNED_CHECK_RANGE_ENDPOINT', 'https://api.pwnedpasswords.com/range/'),

    /**
     * Should validation failures through this service be noted in laravel log? Can be disabled if using in
     * advisory capacity rather than hard block
     */
    'log_failures' => env('PWNED_CHECK_LOG_FAILURES', true),
];
