<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Sidecar Collections
    |--------------------------------------------------------------------------
    |
    | Map collection handles to Sidecar drivers. Each driver adapts an external
    | content directory (e.g. a LaraDocs `docs/` folder or Jigsaw
    | `source/docs/`) into a Statamic collection editable in the Control Panel.
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

        // 'docs' => [
        //     'driver' => 'jigsaw',
        //     'directory' => base_path('source/docs'),
        //     // 'navigation' => base_path('navigation.php'),
        //     // 'url_prefix' => 'docs',
        // ],

    ],

];
