<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Active Static Caching Strategy
    |--------------------------------------------------------------------------
    |
    | To enable Static Caching, you should choose a strategy from the ones
    | you have defined below. Leave this null to disable static caching.
    |
    */

    'strategy' => env('STATAMIC_STATIC_CACHING_STRATEGY', null),

    /*
    |--------------------------------------------------------------------------
    | Caching Strategies
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the static caching strategies for your
    | application as well as their drivers.
    |
    | Supported drivers: "application", "file"
    |
    */

    'strategies' => [

        'half' => [
            'driver' => 'application',
            'expiry' => null,
        ],

        'full' => [
            'driver' => 'file',
            'path' => public_path('static'),
            'lock_hold_length' => 0,
            'permissions' => [
                'directory' => 0755,
                'file' => 0644,
            ],
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Exclusions
    |--------------------------------------------------------------------------
    |
    | Here you may define a list of URLs to be excluded from static
    | caching. You may want to exclude URLs containing dynamic
    | elements like contact forms, or shopping carts.
    |
    */

    'exclude' => [

        'class' => null,

        'urls' => [
            //
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Invalidation Rules
    |--------------------------------------------------------------------------
    |
    | Here you may define the rules that trigger when and how content would be
    | flushed from the static cache. See the documentation for more details.
    | If a custom class is not defined, the default invalidator is used.
    |
    | https://statamic.dev/static-caching
    |
    */

    'invalidation' => [

        'class' => null,

        'rules' => [
            //
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Ignoring Query Strings
    |--------------------------------------------------------------------------
    |
    | Statamic will cache pages of the same URL but with different query
    | parameters separately. This is useful for pages with pagination.
    | If you'd like to ignore the query strings, you may do so.
    |
    */

    'ignore_query_strings' => false,

    'allowed_query_strings' => [
        //
    ],

    'disallowed_query_strings' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Nocache
    |--------------------------------------------------------------------------
    |
    | Here you may define where the nocache data is stored.
    |
    | https://statamic.dev/tags/nocache#database
    |
    | Supported drivers: "cache", "database"
    |
    */

    'nocache' => 'cache',

    'nocache_db_connection' => env('STATAMIC_NOCACHE_DB_CONNECTION'),

    'nocache_js_position' => 'body',

    /*
    |--------------------------------------------------------------------------
    | Replacers
    |--------------------------------------------------------------------------
    |
    | Here you may define replacers that dynamically replace content within
    | the response. Each replacer must implement the Replacer interface.
    |
    */

    'replacers' => [
        \Statamic\StaticCaching\Replacers\CsrfTokenReplacer::class,
        \Statamic\StaticCaching\Replacers\NoCacheReplacer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Warm Queue
    |--------------------------------------------------------------------------
    |
    | Here you may define the queue name and connection
    | that will be used when warming the static cache and
    | optionally set the "--insecure" flag by default.
    |
    */

    'warm_queue' => env('STATAMIC_STATIC_WARM_QUEUE'),

    'warm_queue_connection' => env('STATAMIC_STATIC_WARM_QUEUE_CONNECTION'),

    'warm_insecure' => env('STATAMIC_STATIC_WARM_INSECURE', false),

    /*
    |--------------------------------------------------------------------------
    | Shared Error Pages
    |--------------------------------------------------------------------------
    |
    | You may choose to share the same statically generated error page across
    | all errors. For example, the first time a 404 is encountered it will
    | be generated and cached, and then served for all subsequent 404s.
    |
    | This is only supported for half measure.
    |
    */

    'share_errors' => false,

];
