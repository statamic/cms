<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sites
    |--------------------------------------------------------------------------
    |
    | Each site should have root URL that is either relative or absolute. Sites
    | are typically used for localization (eg. English/French) but may also
    | be used for related content (eg. different franchise locations).
    |
    */

    'sites' => [

        'default' => [
            'name' => config('app.name'),
            'locale' => 'en_US',
            'url' => '/',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Localization of entries
    |--------------------------------------------------------------------------
    |
    | When localizing an entry into another site for the first time, Statamic
    | will prompt you to select which site the entry should originate from.
    | You may prefer to bypass the prompt and instead choose to always
    | originate from the root, or always from the site being edited.
    |
    | Supported options: "select", "root", "active"
    |
    */

    'localize_entries_from' => 'select',

];
