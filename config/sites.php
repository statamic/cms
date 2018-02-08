<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Site
    |--------------------------------------------------------------------------
    |
    | The default site will hold the majority of the content on your website.
    | Additional sites with unlocalized content will fall back to the data
    | defined in the default. You should specific a site handle below.
    |
    */

    'default' => 'default',

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
            'name' => 'Statamic',
            'locale' => 'en_US',
            'url' => '/',
        ]

    ]
];