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

    'cache' => [
        'expiry' => 60,
    ],

];
