<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable autosave
    |--------------------------------------------------------------------------
    |
    | THIS IS A EXPERIMENTAL FEATURE. Things may go wrong.
    |
    | Set to true to enable autosave. You must also enable autosave
    | manually in every collection in order for it to work.
    |
    | For example, inside `content/collections/pages.yaml`, add
    | `autosave: 5000` for a 5s interval or `autosave: true`
    | to use the default interval as defined below.
    |
    */

    'enabled' => env('STATAMIC_AUTOSAVE_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Default autosave interval
    |--------------------------------------------------------------------------
    |
    | The default value can be set here generally for all collections.
    | However, it is also possible to manually adjust the value in the
    | blueprint of a collection. By default, this value is set to 5s.
    |
    */

    'interval' => 5000,

];
