<?php

return [

    /*
    |--------------------------------------------------------------------------
    | License Key
    |--------------------------------------------------------------------------
    |
    | The license key for the corresponding domain from your Statamic account.
    | Without a key entered, you will considered to be in Trial Mode.
    |
    | https://docs.statamic.com/knowledge-base/trial-mode
    |
    */

    'license_key' => env('STATAMIC_LICENSE_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default Addons Paths
    |--------------------------------------------------------------------------
    |
    | When generating addons via `php please make:addon`, this path will be
    | used by default. You can still specify custom repository paths in
    | your composer.json, but this is the path used by the generator.
    |
    */

    'addons_path' => base_path('addons'),

    /*
    |--------------------------------------------------------------------------
    | Send the Powered-By Header
    |--------------------------------------------------------------------------
    |
    | Websites like builtwith.com use the X-Powered-By header to determine
    | what technologies are used on a particular site. By default, we'll
    | send this header, but you are absolutely allowed to disable it.
    |
    */

    'send_powered_by_header' => true,

    /*
    |--------------------------------------------------------------------------
    | Maximum PHP memory limit
    |--------------------------------------------------------------------------
    |
    | The maximum memory that will be used when performing intensive operations
    | like image manipulation. Leave blank to use as much as possible. You
    | may either specify bytes or PHP recognized shorthand values.
    |
    | http://php.net/manual/en/faq.using.php#faq.using.shorthandbytes
    |
    */

    'php_max_memory_limit' => null,

    /*
    |--------------------------------------------------------------------------
    | Default Date Format
    |--------------------------------------------------------------------------
    |
    | Any time Carbon is cast to a string in views, it should use this format.
    |
    */

    'date_format' => 'F jS, Y',

    'charset' => 'UTF-8',
    // 'parser_backtrack_limit' => null,
    'timezone' => 'UTC',
    'protect' => [],

    'stache' => [
        'always_update' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Intensive Operations
    |--------------------------------------------------------------------------
    |
    | Sometimes Statamic requires extra resources to complete intensive
    | operations. Here you may configure system resource limits for
    | those rare times when we need to turn things up to eleven!
    |
    */

    'php_memory_limit' => '-1',
    'php_max_execution_time' => '-1',
    'ajax_timeout' => '600000',

];
