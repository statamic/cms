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

    'enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Default autosave interval
    |--------------------------------------------------------------------------
    |
    | The default value may be set here and will apply to all collections.
    | However, it is also possible to manually adjust the value in the
    | each collection's config file. By default, this is set to 5s.
    |
    */

    'interval' => 5000,

];
