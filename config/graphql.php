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
        'forms' => false,
        'sites' => false,
        'users' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Queries
    |--------------------------------------------------------------------------
    |
    | Here you may list queries to be added to the Statamic schema.
    |
    | https://statamic.dev/graphql#custom-queries
    |
    */

    'queries' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Mutations
    |--------------------------------------------------------------------------
    |
    | Here you may list mutations to be added to the Statamic schema.
    |
    | https://statamic.dev/graphql#custom-mutations
    */

    'mutations' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Here you may list middleware to be added to the Statamic schema.
    |
    | https://statamic.dev/graphql#custom-middleware
    |
    */

    'middleware' => [
        //
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

    /*
    |--------------------------------------------------------------------------
    | Introspection
    |--------------------------------------------------------------------------
    |
    | Introspection queries allow a user to see the schema and will power
    | development tools. This is "auto" by default, which will enable
    | it locally and keep it disabled everywhere else for security.
    |
    */

    'introspection' => env('STATAMIC_GRAPHQL_INTROSPECTION_ENABLED', 'auto'),

];
