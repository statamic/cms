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
    | Two Factor Authentication
    |--------------------------------------------------------------------------
    |
    | ...
    |
    */

    'two_factor' => [

        /*
        |--------------------------------------------------------------------------
        | Is two factor enabled?
        |--------------------------------------------------------------------------
        |
        | When enabled, two factor authentication challenges will be presented to
        | users of the Statamic CP. This will direct them to a setup screen on
        | their next page visit, or the next time they sign in.
        |
        */

        'enabled' => env('STATAMIC_TWO_FACTOR_ENABLED', false),

        /*
        |--------------------------------------------------------------------------
        | Role-specific enforcement
        |--------------------------------------------------------------------------
        |
        | Super admins will always require two factor.
        |
        | Provide an array of Role handles that should have two factor enforced,
        | such as:
        |   'enforced_roles' => [
        |       'content_publisher',
        |       'users_admin',
        |   ],
        |
        | An empty array will mean that no roles are enforced.
        |
        | Set to null to enforce for all roles.
        |
        */

        'enforced_roles' => null,

        /*
        |--------------------------------------------------------------------------
        | Blueprint field
        |--------------------------------------------------------------------------
        |
        | The name of the blueprint field handle for the status storage of the
        | user's two factor authentication status (setup and locked).
        |
        */

        'blueprint' => 'two_factor',

        /*
        |--------------------------------------------------------------------------
        | Number of incorrect two factor code attempts
        |--------------------------------------------------------------------------
        |
        | Only a specific number of incorrect attempts are allowed. This helps by
        | locking an account to prevent a bot from brute forcing their way in.
        | This count is incremented on each incorrect code or recovery code
        | attempt. When a challenge is successfully completed, the value
        | resets to zero.
        |
        | Default: 5
        |
        */

        'attempts' => env('STATAMIC_TWO_FACTOR_ATTEMPTS_ALLOWED', 5),

        /*
        |--------------------------------------------------------------------------
        | Two factor code validity
        |--------------------------------------------------------------------------
        |
        | The code validity will keep tabs on the last time the user was asked to
        | complete a two factor challenge. When this period expires, they will
        | be asked to complete another challenge. Stored as the number of
        | minutes.
        |
        | Default: 43200 minutes (30 days)
        |
        | Set to null to disable this feature.
        |
        */

        'validity' => env('STATAMIC_TWO_FACTOR_VALIDITY', 43200),

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
