<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Version
    |--------------------------------------------------------------------------
    |
    | The desired Antlers language version to utilize. Supported values are
    | "runtime" for the modern parser, or "regex" for the legacy parser.
    |
    */

    'version' => 'runtime',

    /*
    |--------------------------------------------------------------------------
    | Debugbar Profiler (Experimental)
    |--------------------------------------------------------------------------
    |
    | Enable the Antlers Profiling tab in the Debugbar. This is a work in
    | progress tool that's being developed to find performance
    | bottlenecks in your Antlers templates.
    |
    */

    'debugbar' => false,

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

];
