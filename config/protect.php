<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default (or site-wide) Scheme
    |--------------------------------------------------------------------------
    |
    | The default scheme will be applied to every page of the site.
    | By default, you probably won't want to protect anything
    | at all, but you are free to select one if necessary.
    |
    */

    'default' => null,

    /*
    |--------------------------------------------------------------------------
    | Static caching.
    |--------------------------------------------------------------------------
    |
    | Default setting for whether or not to allow protected pages in the static
    | cache.
    |
    */

    'cacheable' => null,

    /*
    |--------------------------------------------------------------------------
    | Protection Schemes
    |--------------------------------------------------------------------------
    |
    | Here you may define all of the protection schemes for your application
    | as well as their drivers. You may even define multiple schemes for
    | the same driver to easily protect different types of pages.
    |
    | Supported drivers: "ip_address", "auth", "password"
    |
    */

    'schemes' => [

        'ip_address' => [
            'driver' => 'ip_address',
            'allowed' => ['127.0.0.1'],
            // NOTE: Set `cacheable` to true/false to allow static caching for
            //       this driver.
            // 'cacheable' => null,
        ],

        'logged_in' => [
            'driver' => 'auth',
            'login_url' => '/login',
            'append_redirect' => true,
        ],

        'password' => [
            'driver' => 'password',
            'allowed' => ['secret'],
            'field' => null,
            'form_url' => null,
        ],

    ],

];
