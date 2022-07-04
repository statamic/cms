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
            ],
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

    /*
    |--------------------------------------------------------------------------
    | New User Roles
    |--------------------------------------------------------------------------
    |
    | When registering new users through the user:register_form tag, these
    | roles will automatically be applied to your newly created users.
    |
    */

    'new_user_roles' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | New User Groups
    |--------------------------------------------------------------------------
    |
    | When registering new users through the user:register_form tag, these
    | groups will automatically be applied to your newly created users.
    |
    */

    'new_user_groups' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Brokers
    |--------------------------------------------------------------------------
    |
    | When resetting passwords, Statamic uses an appropriate password broker.
    | Here you may define which broker should be used for each situation.
    | You may want a longer expiry for user activations, for example.
    |
    */

    'passwords' => [
        'resets' => config('auth.defaults.passwords'),
        'activations' => config('auth.defaults.passwords'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Database
    |--------------------------------------------------------------------------
    |
    | Here you may configure the database connection and its table names.
    |
    */

    'database' => config('database.default'),

    'tables' => [
        'users' => 'users',
        'role_user' => 'role_user',
        'group_user' => 'group_user',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | By default, Statamic will use the `web` authentication guard. However,
    | if you want to run Statamic alongside the default Laravel auth
    | guard, you can configure that for your cp and/or frontend.
    |
    */

    'guards' => [
        'cp' => 'web',
        'web' => 'web',
    ],

];
