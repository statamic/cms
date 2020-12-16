<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Control Panel
    |--------------------------------------------------------------------------
    |
    | Whether the Control Panel should be enabled, and through what route.
    |
    */

    'enabled' => env('CP_ENABLED', true),

    'route' => env('CP_ROUTE', 'cp'),

    /*
    |--------------------------------------------------------------------------
    | Start Page
    |--------------------------------------------------------------------------
    |
    | When a user logs into the Control Panel, they will be taken here.
    | For example: "dashboard", "collections/pages", etc.
    |
    */

    'start_page' => 'dashboard',

    /*
    |--------------------------------------------------------------------------
    | Dashboard Widgets
    |--------------------------------------------------------------------------
    |
    | Here you may define any number of dashboard widgets. You're free to
    | use the same widget multiple times in different configurations.
    |
    */

    'widgets' => [
        'getting_started',
    ],

    /*
    |--------------------------------------------------------------------------
    | Date Format
    |--------------------------------------------------------------------------
    |
    | When a date is encountered throughout the Control Panel, it will be
    | rendered in the following format. Any PHP date variables are permitted.
    |
    */

    'date_format' => 'Y-m-d',

    /*
    |--------------------------------------------------------------------------
    | Pagination
    |--------------------------------------------------------------------------
    |
    | The numbers of items to show on each paginated page.
    |
    */

    'pagination_size' => 50,

    /*
    |--------------------------------------------------------------------------
    | Links to Documentation
    |--------------------------------------------------------------------------
    |
    | Show contextual links to documentation throughout the Control Panel.
    |
    */

    'link_to_docs' => env('STATAMIC_LINK_TO_DOCS', true),

    /*
    |--------------------------------------------------------------------------
    | Support Link
    |--------------------------------------------------------------------------
    |
    | Set the location of the support link in the "Useful Links" header
    | dropdown. Use 'false' to remove it entirely.
    |
    */

    'support_url' => env('STATAMIC_SUPPORT_URL', 'https://statamic.com/support'),

    /*
    |--------------------------------------------------------------------------
    | Login Theme
    |--------------------------------------------------------------------------
    |
    | Optionally spice up the login screen and choose
    | between "rad" or "business" modes.
    |
    */

    'login_theme' => env('STATAMIC_LOGIN_THEME', 'rad'),

    /*
    |--------------------------------------------------------------------------
    | White Labeling
    |--------------------------------------------------------------------------
    |
    | When in Pro Mode you may replace the Statamic name and logo with those
    | your choosing, as long as they're not intended to mislead.
    |
    */

    'cms_name' => env('STATAMIC_CMS_NAME', 'Statamic'),

    'logo_url' => env('STATAMIC_LOGO_URL', null),

];
