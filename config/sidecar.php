<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sidecar Collections
    |--------------------------------------------------------------------------
    |
    | Map collection handles to Sidecar drivers. Each driver adapts an external
    | content directory (e.g. a LaraDocs `docs/` folder) into a Statamic
    | collection editable in the Control Panel.
    |
    | @experimental
    |
    */

    'collections' => [

        // 'docs' => [
        //     'driver' => 'laradocs',
        //     'directory' => base_path('docs'),
        //     // 'title' => 'Documentation',
        //     // 'blueprint' => 'custom_docs',
        // ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Known Driver Packages
    |--------------------------------------------------------------------------
    |
    | Used by `php please sidecar:install` to detect installed SSG packages and
    | offer to install the matching Sidecar driver.
    |
    */

    'packages' => [
        'petebishwhip/laradocs' => 'statamic/sidecar-laradocs',
    ],

];
