<?php

return [

    /*
    |--------------------------------------------------------------------------
    | User Repository
    |--------------------------------------------------------------------------
    |
    | Statamic assumes you will be storing users in the filesystem inside the
    | "Stache" datastore. You are free to customize the storage method here.
    |
    | Supported: "stache", "eloquent", "redis"
    |
    */

    'repository' => env('STATAMIC_USERS', 'file'),

    'repositories' => [

        'file' => [
            'driver' => 'file',
            'paths' => [
                'users' => base_path('users'),
                'roles' => config_path('statamic/user_roles.yaml'),
                'groups' => config_path('statamic/user_groups.yaml'),
            ]
        ],

        'eloquent' => [
            'driver' => 'eloquent',
            'model' => \Statamic\Eloquent\Auth\Model::class,
        ],

    ],



    /*
    |--------------------------------------------------------------------------
    | Login type
    |--------------------------------------------------------------------------
    |
    | By default, Statamic uses the username field for authentication, and
    | doesn't require email addresses. You may swap this behavior.
    |
    | Supported: "username" or "email"
    |
    */

    'login_type' => 'username',

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
    | User Roles
    |--------------------------------------------------------------------------
    |
    | One or more roles may be assigned to a user granting them permission to
    | interact with various parts of the system. Roles are stored in a YAML
    | file for ease of editing and for the Control Panel to write into.
    |
    */

    'roles' => [

        'path' => config_path('statamic/user_roles.yaml'),

        'role' => \Statamic\Auth\Role::class,
        'repository' => \Statamic\Auth\RoleRepository::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | User Groups
    |--------------------------------------------------------------------------
    |
    | A user group can be assigned one or more roles, then users may be added
    | to the group. A user will then inherit the roles and permissions from
    | the corresponding groups. Groups are stored in a YAML file for ease
    | of editing and for the Control Panel to write into.
    |
    */

    'groups' => [

        'path' => config_path('statamic/user_groups.yaml'),

        'group' => \Statamic\Auth\UserGroup::class,
        'repository' => \Statamic\Auth\UserGroupRepository::class,

    ],

];
