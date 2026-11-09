<?php

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
        //
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
