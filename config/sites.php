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
    | Transliteration.
    |--------------------------------------------------------------------------
    |
    | Settings controlling how transliteration is done.
    | By default symbols like ampersand (&) are stripped out.
    |
    */

    'transliteration' => [
        'symbols' => false,
    ],

];
