<?php

return [

    /*
    |--------------------------------------------------------------------------
    | API
    |--------------------------------------------------------------------------
    |
    | Whether the API should be enabled, and through what route. You
    | can enable or disable the whole API, and expose individual
    | resources per environment, depending on your site needs.
    |
    | https://statamic.dev/content-api#enable-the-api
    |
    */

    'enabled' => env('STATAMIC_API_ENABLED', false),

    'resources' => [
        'collections' => false,
        'navs' => false,
        'taxonomies' => false,
        'assets' => false,
        'globals' => false,
        'forms' => false,
        'users' => false,
    ],

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

    /*
    |--------------------------------------------------------------------------
    | Exclude Keys
    |--------------------------------------------------------------------------
    |
    | Here you may provide an array of keys to be excluded from API responses.
    | For example, you may want to hide things like edit_url, api_url, etc.
    |
    */

    'excluded_keys' => [
        //
    ],

];
