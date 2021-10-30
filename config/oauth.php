<?php

return [

    'enabled' => env('STATAMIC_OAUTH_ENABLED', false),

    'email_login_enabled' => true,

    'providers' => [
        // 'github',
    ],

    'routes' => [
        'login' => 'oauth/{provider}',
        'callback' => 'oauth/{provider}/callback',
    ],

];
