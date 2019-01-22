<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Control Panel
    |--------------------------------------------------------------------------
    |
    | Whether the API should be enabled, and through what route.
    |
    */

    'enabled' => env('API_ENABLED', true),

    'route' => env('API_ROUTE', 'api'),

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | The numbers of items to show on each paginated page.
    |
    */

    'pagination_size' => 50,

];
