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
    | More info: https://statamic.dev/routing
    |
    */

    'enabled' => true,

    /*
    |--------------------------------------------------------------------------
    | Enable Route Bindings
    |--------------------------------------------------------------------------
    |
    | Whether route bindings for Statamic repositories (entry, taxonomy,
    | collections, etc) are enabled for front end routes. This may be
    | useful if you want to make your own custom routes with them.
    |
    */

    'bindings' => false,

    /*
    |--------------------------------------------------------------------------
    | Enable Absolute Domain Redirects
    |--------------------------------------------------------------------------
    |
    | Whether to redirect absolute domains ending in a dot to the
    | corresponding domain without a dot. This is useful for
    | preventing issues with browsers and DNS resolvers.
    |
    | see: https://www.rfc-editor.org/rfc/rfc1035#:~:text=Domain%20names%20that%20end%20in%20a%20dot%20are%20called%0Aabsolute%2C%20and%20are%20taken%20as%20complete
    |
    */

    'absolute_domain_redirect' => true,

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

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Define the middleware that will be applied to the web route group.
    |
    */

    'middleware' => 'web',

];
