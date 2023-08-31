<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Forms Path
    |--------------------------------------------------------------------------
    |
    | Where your form YAML files are stored.
    |
    */

    'forms' => resource_path('forms'),

    /*
    |--------------------------------------------------------------------------
    | Submissions Path
    |--------------------------------------------------------------------------
    |
    | Where your form submissions are stored.
    |
    */

    'submissions' => storage_path('forms'),

    /*
    |--------------------------------------------------------------------------
    | Email View Folder
    |--------------------------------------------------------------------------
    |
    | The folder under resources/views where your email templates are found.
    |
    */

    'email_view_folder' => null,

    /*
    |--------------------------------------------------------------------------
    | Send Email Job
    |--------------------------------------------------------------------------
    |
    | The class name of the job that will be used to send an email.
    |
    */

    'send_email_job' => \Statamic\Forms\SendEmail::class,

    /*
    |--------------------------------------------------------------------------
    | CSV Export Delimiter
    |--------------------------------------------------------------------------
    |
    | Statamic will use this character as delimiter for csv exports.
    |
    */

    'csv_delimiter' => ',',

    /*
    |--------------------------------------------------------------------------
    | CSV Export Headings
    |--------------------------------------------------------------------------
    |
    | If true Statamic will use the field handles for headings
    | if false Statamic will use the field display for headings
    |
    */

    'csv_use_handles_for_headings' => true,

];
