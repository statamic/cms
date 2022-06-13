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
    'enabled' => false,

    /*
    |--------------------------------------------------------------------------
    | Do you understand the risks?
    |--------------------------------------------------------------------------
    |
    | As this is experimental and might break, be aware that you souldn't use
    | this feature in production. If you do or if anything breaks, this is
    | expected and will probably happen.
    |
    | Set `understood` to true, to confirm that you do understand the risks.
    |
    */
    'understood' => false,

    /*
    |--------------------------------------------------------------------------
    | Autosave interval
    |--------------------------------------------------------------------------
    |
    | Here you can specify when it should be checked if something has changed
    | at an entry and should be saved. By default, this value is set to 5000 ms.
    |
    */
    'interval' => 5000,
];
