<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default view names
    |--------------------------------------------------------------------------
    |
    | When loading a particular content type (page, entry, term, etc) and a
    | template hasn't been explicitly defined, Statamic will attempt to
    | load one with the corresponding default template names below.
    |
    */

    'views' => [
        'layout' => 'layout',
        'entry' => 'post',
        'term' => 'term',
        'default' => 'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | Default errors directory
    |--------------------------------------------------------------------------
    |
    | When loading an error page, like a 404, Statamic will attempt to load a
    | template with the same name as the error code (ie. errors/404.html).
    | You may change the option below to look in a different directory.
    |
    */

    'errors_directory' => 'errors',

    /*
    |--------------------------------------------------------------------------
    | Default blueprints
    |--------------------------------------------------------------------------
    |
    | When creating or editing a particular content type in the Control Panel
    | and a blueprint hasn't been explicitly defined, Statamic will attempt
    | to load one with the corresponding default blueprint handles below.
    |
    */

    'blueprints' => [
        'entry' => 'entry',
        'term' => 'term',
        'asset' => 'asset',
        'default' => 'default',
    ],

];
