<?php

return [

    /*
    |--------------------------------------------------------------------------
    | GraphQL
    |--------------------------------------------------------------------------
    |
    | Here you may enable the GraphQL API, and select which resources
    | are available to be queried, depending on your site's needs.
    |
    | https://statamic.dev/graphql
    |
    */

    'enabled' => env('STATAMIC_GRAPHQL_ENABLED', false),

    'resources' => [
        'collections' => false,
        'navs' => false,
        'taxonomies' => false,
        'assets' => false,
        'globals' => false,
        'sites' => false,
        'users' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | By default, Statamic will cache each request until the specified
    | expiry, or until content is changed. See the documentation for
    | more details on how to customize your cache implementation.
    |
    | https://statamic.dev/graphql#caching
    |
    */

    'cache' => [
        'expiry' => 60,
    ],

];
