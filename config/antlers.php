<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Version
    |--------------------------------------------------------------------------
    |
    | The desired Antlers language version to utilize. Possible values are:
    |   - regex: Utilize pre-3.3 Antlers. Appropriate for existing sites.
    |   - runtime: Utilizes >= 3.3 Antlers, recommended for new sites.
    |
    */

    'version' => 'regex',

    /*
    |--------------------------------------------------------------------------
    | Guarded Variables
    |--------------------------------------------------------------------------
    |
    | Any variable pattern that appears in this list will not be allowed
    | in any Antlers template, including any user-supplied values.
    |
    */

    'guardedVariables' => [
        'config.app.key',
    ],

    /*
    |--------------------------------------------------------------------------
    | Guarded Tags
    |--------------------------------------------------------------------------
    |
    | Any tag pattern that appears in this list will not be allowed
    | in any Antlers template, including any user-supplied values.
    |
    */

    'guardedTags' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | Guarded Modifiers
    |--------------------------------------------------------------------------
    |
    | Any modifier pattern that appears in this list will not be allowed
    | in any Antlers template, including any user-supplied values.
    |
    */

    'guardedModifiers' => [

    ],

    /*
    |--------------------------------------------------------------------------
    | DebugBar Integration
    |--------------------------------------------------------------------------
    |
    | Antlers integrates with Laravel DebugBar to bring more detail to your
    | debugging experience. On complex pages however, this can be slow.
    | Feel free to disable this setting for a snappier DebugBar.
    |
    */

    'debugbar' => [
        'prettyPrintVariables' => true,
    ],

];
