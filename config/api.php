<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API
    |--------------------------------------------------------------------------
    |
    | Whether the API should be enabled, and through what route.
    |
    */

    'enabled' => env('STATAMIC_API_ENABLED', false),

    'route' => env('STATAMIC_API_ROUTE', 'api'),

    /*
    |--------------------------------------------------------------------------
    | Middleware & Authentication
    |--------------------------------------------------------------------------
    |
    | Define the middleware / middleware group that will be applied to the
    | API route group. If you want to externally expose this API, here
    | you can configure a middleware based authentication layer.
    |
    */

    'middleware' => env('STATAMIC_API_MIDDLEWARE', 'api'),

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | The numbers of items to show on each paginated page.
    |
    */

    'pagination_size' => 50,

    /*
    |--------------------------------------------------------------------------
    | Caching
    |--------------------------------------------------------------------------
    |
    | By default, Statamic will cache each endpoint until the specified
    | expiry, or until content is changed. See the documentation for
    | more details on how to customize your cache implementation.
    |
    | https://statamic.dev/content-api#caching
    |
    */

    'cache' => [
        'expiry' => 60,
    ],

];
