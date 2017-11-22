<?php

return [

    'license_key' => '',

    'locales' => [
        'en' => [
            'name' => 'English',
            'full' => 'en_US',
            'url' => '/',
        ],
    ],

    'default_extension' => 'md',
    'send_powered_by_header' => true,

    'ensure_unique_ids' => true,

    # The max amount of memory Statamic will try to use when performing memory intensive
    # operations like image manipulation. Leave null to use as much memory as possible.
    'php_max_memory_limit' => null,

];