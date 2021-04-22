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
            'directory' => base_path(env('STATAMIC_STORE_TAXONOMIES', 'content/taxonomies')),
        ],

        'terms' => [
            'class' => Stores\TermsStore::class,
            'directory' => base_path(env('STATAMIC_STORE_TERMS', 'content/taxonomies')),
        ],

        'collections' => [
            'class' => Stores\CollectionsStore::class,
            'directory' => base_path(env('STATAMIC_STORE_COLLECTIONS', 'content/collections')),
        ],

        'entries' => [
            'class' => Stores\EntriesStore::class,
            'directory' => base_path(env('STATAMIC_STORE_ENTRIES', 'content/collections')),
        ],

        'navigation' => [
            'class' => Stores\NavigationStore::class,
            'directory' => base_path(env('STATAMIC_STORE_NAVIGATION','content/navigation')),
        ],

        'collection-trees' => [
            'class' => Stores\CollectionTreeStore::class,
            'directory' => base_path(env('STATAMIC_STORE_COLLECTION_TREES','content/trees/collections')),
        ],

        'nav-trees' => [
            'class' => Stores\NavTreeStore::class,
            'directory' => base_path(env('STATAMIC_STORE_NAVIGATION_TREES','content/trees/navigation')),
        ],

        'globals' => [
            'class' => Stores\GlobalsStore::class,
            'directory' => base_path(env('STATAMIC_STORE_GLOBALS','content/globals')),
        ],

        'asset-containers' => [
            'class' => Stores\AssetContainersStore::class,
            'directory' => base_path(env('STATAMIC_STORE_ASSET_CONTAINERS','content/assets')),
        ],

        'assets' => [
            'class' => Stores\AssetsStore::class,
        ],

        'users' => [
            'class' => Stores\UsersStore::class,
            'directory' => base_path(env('STATAMIC_STORE_USERS','users')),
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
