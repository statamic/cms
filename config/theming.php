<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Smartypants
    |--------------------------------------------------------------------------
    |
    | With this enabled, the "content" field will automatically be run through
    | the smartypants modifier which translates typographical elements into
    | their "smart" versions, eg. straight quotes (") into curly ones (”)
    |
    */

    'smartypants' => false,

    /*
    |--------------------------------------------------------------------------
    | Markdown Hard Wrapping
    |--------------------------------------------------------------------------
    |
    | Normally, line breaks are created in Markdown by ending lines
    | with two spaces. You can enable this option to get a more
    | traditional experience seen on websites like Github.
    |
    */

    'markdown_hard_wrap' => false,

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
        'page' => 'default',
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
    | Default fieldsets
    |--------------------------------------------------------------------------
    |
    | When creating or editing a particular content type in the Control Panel
    | and a fieldset hasn't been explicitly defined, Statamic will attempt
    | to load one with the corresponding default fieldset names below.
    |
    */

    'fieldsets' => [
        'page' => 'page',
        'entry' => 'entry',
        'term' => 'term',
        'asset' => 'asset',
        'default' => 'default',
    ],

    /*
    |--------------------------------------------------------------------------
    | Use dedicated view directories
    |--------------------------------------------------------------------------
    |
    | In v3, all of your view files are located within a single "views"
    | directory. Enabling this option will revert to a v2 style where
    | you must separate out "layouts", "templates", and "partials".
    |
    */

    'dedicated_view_directories' => false,

];