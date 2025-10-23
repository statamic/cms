<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Allow password logins to be used when user has a passkey
    |--------------------------------------------------------------------------
    |
    | Whether or not the password field should be shown to users that
    | have set up a passkey, or whether it should be hidden.
    |
    */

    'allow_password_login_with_passkey' => true,

    /*
    |--------------------------------------------------------------------------
    | Remember Me
    |--------------------------------------------------------------------------
    |
    | Whether or not the "remember me" functionality should be used when
    | authenticating using WebAuthn. When enabled, the user will remain
    | logged in indefinitely, or until they manually log out.
    |
    */

    'remember_me' => true,

    /*
    |--------------------------------------------------------------------------
    | Model
    |--------------------------------------------------------------------------
    |
    | When using eloquent passkeys you can specify the model you want to use
    |
    */

    'model' => \Statamic\Auth\Eloquent\WebAuthnModel::class,

];
