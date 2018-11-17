<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Automatic Configuration
    |--------------------------------------------------------------------------
    |
    | By default, Laravel comes equipped to store users in a database.
    | However, Statamic knows you probably want to store them in files and
    | will try to override the configuration to use its our custom user provider.
    |
    | You are free to disable this and configure it manually. You may want to
    | do this if you have a custom setup or plan to store users in a database.
    |
    */

    'auto_configure' => true,


    'driver' => 'file',
    'redis_write_file' => true,
    'login_type' => 'username',
    'new_user_roles' => [],
    'gravatar' => true,

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

        'role' => \Statamic\Permissions\Role::class,
        'repository' => \Statamic\Permissions\RoleRepository::class,

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

        'group' => \Statamic\Permissions\UserGroup::class,

    ],

];
