<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Repository
    |--------------------------------------------------------------------------
    |
    | Statamic uses a repository to get users, roles, groups, and their
    | relationships from specified storage locations. The file driver
    | gets it from disk, while the eloquent driver gets from a DB.
    |
    | Supported: "file", "eloquent"
    |
    */

    'repository' => 'file',

    'repositories' => [

        'file' => [
            'driver' => 'file',
            'paths' => [
                'users' => base_path('users'),
                'roles' => resource_path('users/roles.yaml'),
                'groups' => resource_path('users/groups.yaml'),
            ]
        ],

        'eloquent' => [
            'driver' => 'eloquent',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Avatars
    |--------------------------------------------------------------------------
    |
    | User avatars are initials by default, with custom options for services
    | like Gravatar.com.
    |
    | Supported: "initials", "gravatar", or a custom class name.
    |
    */

    'avatars' => 'initials',

];
