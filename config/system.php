<?php

return [

    /*
    |--------------------------------------------------------------------------
    | License Key
    |--------------------------------------------------------------------------
    |
    | The license key for the corresponding domain from your Statamic account.
    | Without a key entered, your app will considered to be in Trial Mode.
    |
    | https://statamic.dev/licensing#trial-mode
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
    | Default Date Format
    |--------------------------------------------------------------------------
    |
    | Any time a Carbon date is cast to a string, it should use this format.
    | You can customize this format using PHP's date string constants.
    |
    | https://www.php.net/manual/en/function.date.php
    |
    */

    'date_format' => 'F jS, Y',

    /*
    |--------------------------------------------------------------------------
    | Default Character Set
    |--------------------------------------------------------------------------
    |
    | Statamic will use this character set when performing specific string
    | encoding and decoding operations; This does not apply everywhere.
    |
    */

    'charset' => 'UTF-8',

    /*
    |--------------------------------------------------------------------------
    | Track Last Update
    |--------------------------------------------------------------------------
    |
    | Statamic will automatically set an `updated_at` timestamp (along with
    | `updated_by`, where applicable) when specific content is updated.
    | In some situations, you may wish disable this functionality.
    |
    */

    'track_last_update' => true,

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
    'pcre_backtrack_limit' => '-1',

];
