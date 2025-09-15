<?php

use Statamic\CP\Color;

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
    | Authentication
    |--------------------------------------------------------------------------
    |
    | Whether the Control Panel's authentication pages should be enabled,
    | and where users should be redirected in order to authenticate.
    |
    */

    'auth' => [
        'enabled' => true,
        'redirect_to' => null,
    ],

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
    | Pagination
    |--------------------------------------------------------------------------
    |
    | Here you may define the default pagination size as well as the options
    | the user can select on any paginated listing in the Control Panel.
    |
    */

    'pagination_size' => 50,

    'pagination_size_options' => [10, 25, 50, 100, 500],

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
    | Theme
    |--------------------------------------------------------------------------
    |
    | Adjust the colors used in the Control Panel. Use the Color class
    | to easily access the Tailwind CSS color palette.
    |
    */

    'theme' => [
        // 'grays' => Color::Zinc,

        // 'primary' => Color::Zinc[800],
        // 'success' => Color::Green[400],
        // 'danger' => Color::Red[600],

        // 'ui-accent' => Color::Zinc[800],
        // 'dark-ui-accent' => Color::Zinc[200],

        // 'body-bg' => Color::Zinc[100],
        // 'body-border' => Color::Transparent,
        // 'dark-body-bg' => Color::Zinc[900],
        // 'dark-body-border' => Color::Zinc[950],

        // 'global-header-bg' => Color::Zinc[800],
        // 'dark-global-header-bg' => Color::Zinc[800],

        // 'content-bg' => "linear-gradient(to right, hsl(0,0%,99%), #ffffff)",
        // 'content-border' => Color::Zinc[200],
        // 'dark-content-bg' => Color::Zinc[900],
        // 'dark-content-border' => Color::Zinc[950],

        // 'progress-bar' => Color::Volt,

        // 'switch-bg' => Color::Green[500],
        // 'dark-switch-bg' => Color::Green[600],
    ],

    /*
    |--------------------------------------------------------------------------
    | White Labeling
    |--------------------------------------------------------------------------
    |
    | When in Pro Mode you may replace the Statamic name, logo, favicon,
    | and add your own CSS to the control panel to match your
    | company or client's brand.
    |
    */

    'custom_cms_name' => env('STATAMIC_CUSTOM_CMS_NAME', 'Statamic'),

    'custom_logo_url' => env('STATAMIC_CUSTOM_LOGO_URL', null),

    'custom_dark_logo_url' => env('STATAMIC_CUSTOM_DARK_LOGO_URL', null),

    'custom_logo_text' => env('STATAMIC_CUSTOM_LOGO_TEXT', null),

    'custom_favicon_url' => env('STATAMIC_CUSTOM_FAVICON_URL', null),

    'custom_css_url' => env('STATAMIC_CUSTOM_CSS_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Thumbnails
    |--------------------------------------------------------------------------
    |
    | Here you may define additional CP asset thumbnail presets.
    |
    */

    'thumbnail_presets' => [
        // 'medium' => 800,
    ],
];
