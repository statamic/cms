<?php

use Statamic\Stache\Stores;

return [

    /*
    |--------------------------------------------------------------------------
    | File Watcher
    |--------------------------------------------------------------------------
    |
    | File changes will be noticed and data will be updated accordingly.
    | This can be disabled to reduce overhead, but you will need to
    | either update the cache manually or use the Control Panel.
    |
    */

    'watcher' => env('STATAMIC_STACHE_WATCHER', true),

    /*
    |--------------------------------------------------------------------------
    | Stores
    |--------------------------------------------------------------------------
    |
    | Here you may configure which stores are used inside the Stache.
    |
    */

    'stores' => [

        'taxonomies' => [
            'class' => Stores\TaxonomiesStore::class,
            'directory' => base_path('content/taxonomies'),
        ],

        'terms' => [
            'class' => Stores\TermsStore::class,
            'directory' => base_path('content/taxonomies'),
        ],

        'collections' => [
            'class' => Stores\CollectionsStore::class,
            'directory' => base_path('content/collections'),
        ],

        'entries' => [
            'class' => Stores\EntriesStore::class,
            'directory' => base_path('content/collections'),
        ],

        'navigation' => [
            'class' => Stores\NavigationStore::class,
            'directory' => base_path('content/navigation'),
        ],

        'globals' => [
            'class' => Stores\GlobalsStore::class,
            'directory' => base_path('content/globals'),
        ],

        'asset-containers' => [
            'class' => Stores\AssetContainersStore::class,
            'directory' => base_path('content/assets'),
        ],

        'users' => [
            'class' => Stores\UsersStore::class,
            'directory' => base_path('users'),
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Indexes
    |--------------------------------------------------------------------------
    |
    | Here you may define any additional indexes that will be inherited
    | by each store in the Stache. You may also define indexes on a
    | per-store level by adding an "indexes" key to its config.
    |
    */

    'indexes' => [
        //
    ],

    /*
    |--------------------------------------------------------------------------
    | Locking
    |--------------------------------------------------------------------------
    |
    | In order to prevent concurrent requests from updating the Stache at
    | the same and wasting resources, it will be "locked" so subsequent
    | requests will have to wait until the first has been completed.
    |
    | https://statamic.dev/stache#locks
    |
    */

    'lock' => [
        'enabled' => true,
        'timeout' => 30,
    ],

];
