<?php

return [

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

    'queries' => [
        //
    ],

    'middleware' => [
        //
    ],

    'cache' => [
        'expiry' => 60,
    ],

];
