<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Debugbar
    |--------------------------------------------------------------------------
    |
    | Here you may specify whether the Antlers profiler should be added
    | to the Laravel Debugbar. This is incredibly useful for finding
    | performance impacts within any of your Antlers templates.
    |
    */

    'debugbar' => env('STATAMIC_ANTLERS_DEBUGBAR', true),

    /*
    |--------------------------------------------------------------------------
    | Tracing
    |--------------------------------------------------------------------------
    |
    | When tracing is enabled, every tracer listed here is resolved from the
    | container and notified as every Antlers node is entered and exited.
    |
    */

    'tracing' => env('STATAMIC_ANTLERS_TRACING', false),

    'tracers' => [],

    /*
    |--------------------------------------------------------------------------
    | HTML Instrumentation
    |--------------------------------------------------------------------------
    |
    | When enabled, rendered Antlers regions are wrapped in HTML comments and
    | attributes that point back to the templates they came from. Analysis
    | is cached by template content; set "cache" to false to skip that.
    |
    */

    'instrumentation' => [
        'enabled' => env('STATAMIC_ANTLERS_INSTRUMENTATION', false),
        'cache' => env('STATAMIC_ANTLERS_INSTRUMENTATION_CACHE', true),
        'comments' => true,
        'prefix' => 'antlers',
        'attributes' => [],
        'metadata' => [],
        'componentPrefixes' => [],
    ],

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
    | User content allowlists
    |--------------------------------------------------------------------------
    |
    | These control which tags and modifiers will be permitted in user-supplied
    | Antlers (e.g. fields with `antlers: true`). Include the literal string
    | `@default` in the array to merge Statamic's defaults with your own.
    |
    */

    // 'allowedContentTags' => [
    //     '@default',
    //     'foo:*',
    // ],

    // 'allowedContentModifiers' => [
    //     '@default',
    //     'foo'
    // ],

];
