<?php

return [

    'enabled' => env('STATAMIC_GRAPHQL_ENABLED', false),

    'queries' => [
        'entries' => false,
        'collections' => false,
        'assets' => false,
        'asset-containers' => false,
        'taxonomies' => false,
        'taxonomy-terms' => false,
        'globals' => false,
        'navs' => false,
        'sites' => false,
        'users' => false,
    ],

    'cache' => [
        'expiry' => 60,
    ],

];
