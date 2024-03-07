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

    'repository' => 'eloquent',

    'repositories' => [

        'file' => [
            'driver' => 'file',
            'paths' => [
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
    | Registration form honeypot field
    |--------------------------------------------------------------------------
    |
    | When registering new users through the user:register_form tag,
    | specify the field to act as a honeypot for bots
    |
    */

    'registration_form_honeypot_field' => null,

    /*
    |--------------------------------------------------------------------------
    | User Wizard Invitation Email
    |--------------------------------------------------------------------------
    |
    | When creating new users through the wizard in the control panel,
    | you may choose whether to be able to send an invitation email.
    | Setting to true will give the user the option. But setting
    | it to false will disable the invitation option entirely.
    |
    */

    'wizard_invitation' => true,

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
        'roles' => false,
        'group_user' => 'group_user',
        'groups' => false,
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

    /*
    |--------------------------------------------------------------------------
    | Impersonation
    |--------------------------------------------------------------------------
    |
    | Here you can configure if impersonation is available, and what URL to
    | redirect to after impersonation begins.
    |
    */

    'impersonate' => [
        'enabled' => env('STATAMIC_IMPERSONATE_ENABLED', true),
        'redirect' => env('STATAMIC_IMPERSONATE_REDIRECT', null),
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Sorting
    |--------------------------------------------------------------------------
    |
    | Here you may configure the default sort behavior for user listings.
    |
    */

    'sort_field' => 'email',
    'sort_direction' => 'asc',

];
