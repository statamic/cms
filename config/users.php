<?php

return [
    'driver' => 'file',
    'redis_write_file' => true,
    'login_type' => 'username',
    'new_user_roles' => [],
    'enable_gravatar' => true,

    'roles' => [
        'admin' => [
            'title' => 'Admin',
            'permissions' => [
                'super',
            ]
        ],
    ],
];