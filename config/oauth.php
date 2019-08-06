<?php

return [

    'enabled' => env('STATAMIC_OAUTH_ENABLED', false),

    'providers' => [
        // github
    ],

    'routes' => [
        'redirect' => 'oauth/{provider}',
        'callback' => 'oauth/{provider}/callback'
    ],

];