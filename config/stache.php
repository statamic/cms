<?php

use Statamic\Stache\Stores;

return [

    'stores' => [

        'collections' => [
            'class' => Stores\CollectionsStore::class,
            'directory' => base_path('content/collections'),
        ],

        'entries' => [
            'class' => Stores\EntriesStore::class,
            'directory' => base_path('content/collections'),
        ],

        'structures' => [
            'class' => Stores\StructuresStore::class,
            'directory' => base_path('content/structures'),
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

    ]

];
