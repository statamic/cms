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
    | File Uploads Path
    |--------------------------------------------------------------------------
    |
    | The path (on the file uploads disk) where file uploads are stored
    | before they're converted to an asset or deleted.
    |
    */

    'file_uploads_path' => env('STATAMIC_FORM_UPLOADS_PATH', 'statamic/form-uploads'),

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
    | Connections Cache Store
    |--------------------------------------------------------------------------
    |
    | The cache store used to track when a submission's connections have all
    | finished, so its temporary files can be deleted. A persistent store
    | such as "database" or "redis" is recommended. Null uses the default.
    |
    */

    'connections_cache_store' => null,

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
    | Spam Submissions
    |--------------------------------------------------------------------------
    |
    | Spam submissions are automatically deleted a set number of days after
    | being marked as spam. Files uploaded through form upload fields are
    | kept for the same period, so that submissions marked as not spam
    | can still be finalized. Set this to null to prevent their deletion.
    |
    */

    'delete_spam_submissions_after' => 30,

    /*
    |--------------------------------------------------------------------------
    | Captcha
    |--------------------------------------------------------------------------
    |
    | A captcha can be added to any form from its settings. Supported drivers
    | are "turnstile", "hcaptcha", "recaptcha" (v2 checkbox), "recaptcha_v3"
    | and "null". Captchas stay off until both of the keys have been set.
    |
    | The score and action only apply to reCAPTCHA v3. Visits scoring under
    | the score are refused. The timeout is in seconds, and a provider that
    | can't be reached in time counts as a failed check.
    |
    */

    'captcha' => [
        'driver' => env('STATAMIC_CAPTCHA_DRIVER', 'turnstile'),
        'site_key' => env('STATAMIC_CAPTCHA_SITE_KEY'),
        'secret_key' => env('STATAMIC_CAPTCHA_SECRET_KEY'),
        'score' => 0.5,
        'action' => 'submit',
        'timeout' => 5,
    ],

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
