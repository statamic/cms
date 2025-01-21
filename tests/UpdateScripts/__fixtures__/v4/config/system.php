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
    | Date Format
    |--------------------------------------------------------------------------
    |
    | Whenever a Carbon date is cast to a string on front-end routes, it will
    | use this format. On CP routes, the format defined in cp.php is used.
    | You can customize this format using PHP's date string constants.
    | Setting this value to null will use Carbon's default format.
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
    | Enable Cache Tags
    |--------------------------------------------------------------------------
    |
    | Sometimes you'll want to be able to disable the {{ cache }} tags in
    | Antlers, so here is where you can do that. Otherwise, it will be
    | enabled all the time.
    |
    */

    'cache_tags_enabled' => env('STATAMIC_CACHE_TAGS_ENABLED', true),

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
    'php_max_execution_time' => '0',
    'ajax_timeout' => '600000',
    'pcre_backtrack_limit' => '-1',

    /*
    |--------------------------------------------------------------------------
    | Debugbar Integration
    |--------------------------------------------------------------------------
    |
    | Statamic integrates with Laravel Debugbar to bring more detail to your
    | debugging experience. Here you may adjust various default options.
    |
    */

    'debugbar' => [
        'pretty_print_variables' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | ASCII
    |--------------------------------------------------------------------------
    |
    | During various string manipulations (e.g. slugification), Statamic will
    | need to make ASCII character conversions. Here you may define whether
    | or not extra characters get converted. e.g. "%" becomes "percent".
    |
    */

    'ascii_replace_extra_symbols' => false,

    /*
    |--------------------------------------------------------------------------
    | Update References on Change
    |--------------------------------------------------------------------------
    |
    | With this enabled, Statamic will attempt to update references to assets
    | and terms when moving, renaming, replacing, deleting, etc. This will
    | be queued, but it can disabled as needed for performance reasons.
    |
    */

    'update_references' => true,

    /*
    |--------------------------------------------------------------------------
    | Row ID handle
    |--------------------------------------------------------------------------
    |
    | Rows in Grid, Replicator, and Bard fields will be given a unique ID using
    | the "id" field. You may need your own field named "id", in which case
    | you may customize the handle of the field that Statamic will use.
    |
    */

    'row_id_handle' => 'id',

];
