<?php

return [
    'default' => 'glide',

    'manipulators' => [
        'glide' => [
            'driver' => 'glide',
            'cache' => public_path('img'),
            'url' => 'img',
        ],

        'imgix' => [
            'driver' => 'imgix',
            'domain' => 'example.imgix.net',
        ],
    ],
];
