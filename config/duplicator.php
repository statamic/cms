<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Defaults
    |--------------------------------------------------------------------------
    |
    | Set defaults for duplicated entries.
    |
    */

    'defaults' => [
        'published' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Ignored fields
    |--------------------------------------------------------------------------
    |
    | Configure any fields which should be ignored when duplicating items.
    |
    */

    'ignored_fields' => [
        'assets' => [
            // 'field_handle',
        ],

        'entries' => [
            // 'collection_handle' => [
            //     'field_handle',
            // ],
        ],

        'terms' => [
            // 'taxonomy_handle' => [
            //     'field_handle',
            // ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Fingerprint
    |--------------------------------------------------------------------------
    |
    | Should Duplicator leave a 'fingerprint' on each entry/term/asset it touches
    | so you can tell if it's a duplicated entry or not?
    |
    */

    'fingerprint' => false,

];
