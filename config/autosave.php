<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Enable autosave
    |--------------------------------------------------------------------------
    |
    | THIS IS A EXPERIMENTAL FEATURE. THINGS WILL PROBABLY GO WRONG.
    | Set to true to enable autosave in general. You need to enable
    | autosave manually in every collection, to make it work.
    |
    | For example inside `content/collections/pages.yaml`
    | Add `autosave: true` or `autosave: 5000` for a
    | saving interval of every 5 seconds.
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
