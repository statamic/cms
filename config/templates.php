<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Template Language
    |--------------------------------------------------------------------------
    |
    | The preferred templated language to use when scaffolding templates.
    |
    | Acceptable values are 'blade' and 'antlers'
    */

    'language' => 'antlers',

    /*
    |--------------------------------------------------------------------------
    | Code Style
    |--------------------------------------------------------------------------
    |
    | Here you may configure the code generator's output style.
    |
    */

    'style' => [
        'line_ending' => 'auto',
        'indent_type' => 'space',
        'indent_size' => 4,
        'final_newline' => false,
    ],

    /*
    |--------------------------------------------------------------------------
    | Antlers Settings
    |--------------------------------------------------------------------------
    |
    | Antlers specific template generation settings.
    |
    */

    'antlers' => [
        'use_components' => false,
    ],
];
