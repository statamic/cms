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

];
