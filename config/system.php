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
    | Locales
    |--------------------------------------------------------------------------
    |
    | An array for each locale your website will be localized into.
    |
    */

    'locales' => [
        'en' => [
            'name' => 'English',
            'full' => 'en_US',
            'url' => config('app.url'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default File Extension
    |--------------------------------------------------------------------------
    |
    | When creating content through the Control Panel, this file extension
    | will be used. The extension may imply that certain processing will
    | be made automatically. eg. "md" will render content as Markdown.
    |
    | Supported: "md", "html", "textile"
    |
    */

    'default_extension' => 'md',

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

    'charset' => 'UTF-8',
    // 'parser_backtrack_limit' => null,
    'timezone' => 'UTC',
    'protect' => [],

    'stache' => [
        'always_update' => true,
    ]

];