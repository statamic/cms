<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable Routes
    |--------------------------------------------------------------------------
    |
    | Statamic adds its own routes to the front-end of your site. You are
    | free to disable this behavior.
    |
    | More info: https://docs.statamic.com/routing
    |
    */

    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Routes
    |--------------------------------------------------------------------------
    |
    | Here you may define any template or controller based routes. Each route
    | may contain wildcards and can point to the name of a template or an
    | array containing any data you want passed in to that template.
    |
    | More info: https://docs.statamic.com/routing
    |
    */

    'routes' => [
        // '/' => 'home'
    ],

    /*
    |--------------------------------------------------------------------------
    | Vanity Routes
    |--------------------------------------------------------------------------
    |
    | Vanity URLs are easy to remember aliases that 302 redirect visitors to
    | permanent URLs. For example, you can set https://example.com/hot-dogs
    | to redirect to https://example.com/blog/2019/09/big-sale-on-hot-dogs.
    |
    */

    'vanity' => [
        // '/promo' => '/blog/2019/09/big-sale-on-hot-dogs',
    ],

    /*
    |--------------------------------------------------------------------------
    | Permanent Redirects
    |--------------------------------------------------------------------------
    |
    | While it's recommended to add permanent redirects (301s) on the server
    | for performence, you may also define them here for your convenience.
    |
    */

    'redirect' => [
        // '/here' => '/there',
    ],

    /*
    |--------------------------------------------------------------------------
    | Action Route Prefix
    |--------------------------------------------------------------------------
    |
    | Some extensions may provide routes that go through the frontend of your
    | website. These URLs begin with the following prefix. We've chosen an
    | unobtrusive default but you are free to select whatever you want.
    |
    */

    'action' => '!',

];
