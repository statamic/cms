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

    'watcher' => env('STATAMIC_STACHE_WATCHER', 'auto'),

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    |
    | Here you may configure which Cache Store the Stache uses.
    |
    */

    'cache_store' => null,

    /*
    |--------------------------------------------------------------------------
    | Stores
    |--------------------------------------------------------------------------
    |
    | Here you may configure the stores that are used inside the Stache.
    |
    | https://statamic.dev/stache#stores
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

        'collection-trees' => [
            'class' => Stores\CollectionTreeStore::class,
            'directory' => base_path('content/trees/collections'),
        ],

        'nav-trees' => [
            'class' => Stores\NavTreeStore::class,
            'directory' => base_path('content/trees/navigation'),
        ],

        'globals' => [
            'class' => Stores\GlobalsStore::class,
            'directory' => base_path('content/globals'),
        ],

        'global-variables' => [
            'class' => Stores\GlobalVariablesStore::class,
            'directory' => base_path('content/globals'),
        ],

        'asset-containers' => [
            'class' => Stores\AssetContainersStore::class,
            'directory' => base_path('content/assets'),
        ],

        'assets' => [
            'class' => Stores\AssetsStore::class,
        ],

        'users' => [
            'class' => Stores\UsersStore::class,
            'directory' => base_path('users'),
        ],

        'form-submissions' => [
            'class' => Stores\SubmissionsStore::class,
            'directory' => storage_path('forms'),
        ],

        'revisions' => [
            'class' => Stores\RevisionsStore::class,
            'directory' => env('STATAMIC_REVISIONS_PATH')
                ? base_path(env('STATAMIC_REVISIONS_PATH'))
                : config('statamic.revisions.path', storage_path('statamic/revisions')),
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
    | In order to prevent concurrent requests from updating the Stache at the
    | same time and wasting resources, it will be locked so that subsequent
    | requests will have to wait until the first one has been completed.
    |
    | https://statamic.dev/stache#locks
    |
    */

    'lock' => [
        'enabled' => true,
        'timeout' => 30,
    ],

    /*
    |--------------------------------------------------------------------------
    | Warming Optimization
    |--------------------------------------------------------------------------
    |
    | These options control performance optimizations during Stache warming.
    |
    */

    'warming' => [
        // Enable parallel store processing for faster warming on multi-core systems
        'parallel_processing' => env('STATAMIC_STACHE_PARALLEL_WARMING', false),

        // Maximum number of parallel processes (0 = auto-detect CPU cores)
        'max_processes' => env('STATAMIC_STACHE_MAX_PROCESSES', 0),

        // Minimum number of stores required to enable parallel processing
        'min_stores_for_parallel' => env('STATAMIC_STACHE_MIN_STORES_PARALLEL', 3),

        // Concurrency driver: 'process', 'fork', or 'sync'
        'concurrency_driver' => env('STATAMIC_STACHE_CONCURRENCY_DRIVER', 'process'),
    ],

];
