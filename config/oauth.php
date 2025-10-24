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

    /*
    |--------------------------------------------------------------------------
    | Create User
    |--------------------------------------------------------------------------
    |
    | Whether or not a user account should be created upon authentication
    | with an OAuth provider. If disabled, a user account will be need
    | to be explicitly created ahead of time.
    |
    */

    'create_user' => true,

    /*
    |--------------------------------------------------------------------------
    | Merge User Data
    |--------------------------------------------------------------------------
    |
    | When authenticating with an OAuth provider, the user data returned
    | such as their name will be merged with the existing user account.
    |
    */

    'merge_user_data' => true,

    /*
    |--------------------------------------------------------------------------
    | Unauthorized Redirect
    |--------------------------------------------------------------------------
    |
    | This controls where the user is taken after authenticating with
    | an OAuth provider but their account is unauthorized. This may
    | happen when the create_user option has been set to false.
    |
    */

    'unauthorized_redirect' => null,

    /*
    |--------------------------------------------------------------------------
    | Remember Me
    |--------------------------------------------------------------------------
    |
    | Whether or not the "remember me" functionality should be used when
    | authenticating using OAuth. When enabled, the user will remain
    | logged in indefinitely, or until they manually log out.
    |
    */

    'remember_me' => true,

];
