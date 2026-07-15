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
    | Partial Submissions
    |--------------------------------------------------------------------------
    |
    | Partial submissions are automatically deleted after a set number of days.
    | Set this to null to prevent their automatic deletion.
    |
    */

    'delete_partial_submissions_after' => 7,

    /*
    |--------------------------------------------------------------------------
    | Exporters
    |--------------------------------------------------------------------------
    |
    | Here you may define all the available form submission exporters.
    | You may customize the options within each exporter's array.
    |
    */

    'exporters' => [
        'csv' => [
            'class' => Statamic\Forms\Exporters\CsvExporter::class,
        ],
        'json' => [
            'class' => Statamic\Forms\Exporters\JsonExporter::class,
        ],
    ],

];
