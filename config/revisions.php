<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Revisions
    |--------------------------------------------------------------------------
    |
    | Revisions must be enabled per-collection by adding `revisions: true` to
    | the collection's yaml file. Here you may disable revisions completely
    | in one go. This is useful for disabling revisions per environment.
    |
    */

    'enabled' => env('STATAMIC_REVISIONS_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Storage Path
    |--------------------------------------------------------------------------
    |
    | This is the directory where your revision files will be located. Within
    | here, they will be further organized into collection, site, ID, etc.
    |
    */

    'path' => storage_path('statamic/revisions'),

];
