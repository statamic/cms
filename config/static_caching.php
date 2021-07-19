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
    | Send the Static-Cache Header
    |--------------------------------------------------------------------------
    |
    | Here you may enable the X-Statamic-Static-Cache HTTP response header.
    | This may be useful for debugging.
    |
    | When enabled X-Statamic-Static-Cache will appear in the response headers
    | with either 'hit' or 'miss' depending on if the content was served by
    | the cache, where 'hit' means that it was and 'miss' means that it was not.
    |
    | This will work as expected with the half strategy, however when using the
    | full strategy, misses should still show up in the headers as expected,
    | but because full cache hits aren't processed by Statamic then we won't be able to
    | add the hit status to the X-Statamic-Static-Cache header.
    */

    'send_static_cache_header' => env('STATAMIC_STATIC_CACHING_HEADER', false),

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
        //
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

];
