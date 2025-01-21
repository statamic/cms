<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Git Integration
    |--------------------------------------------------------------------------
    |
    | Whether Statamic's git integration should be enabled. This feature
    | assumes that git is already installed and accessible by your
    | PHP process' server user. For more info, see the docs at:
    |
    | https://statamic.dev/git-automation
    |
    */

    'enabled' => env('STATAMIC_GIT_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Automatically Run
    |--------------------------------------------------------------------------
    |
    | By default, commits are automatically queued when `Saved` or `Deleted`
    | events are fired. If you prefer users to manually trigger commits
    | using the `Git` utility interface, you may set this to `false`.
    |
    | https://statamic.dev/git-automation#committing-changes
    |
    */

    'automatic' => env('STATAMIC_GIT_AUTOMATIC', true),

    /*
    |--------------------------------------------------------------------------
    | Queue Connection
    |--------------------------------------------------------------------------
    |
    | You may choose which queue connection should be used when dispatching
    | commit jobs. Unless specified, the default connection will be used.
    |
    | https://statamic.dev/git-automation#queueing-commits
    |
    */

    'queue_connection' => env('STATAMIC_GIT_QUEUE_CONNECTION'),

    /*
    |--------------------------------------------------------------------------
    | Dispatch Delay
    |--------------------------------------------------------------------------
    |
    | When `Saved` and `Deleted` events queue up commits, you may wish to
    | set a delay time in minutes for each queued job. This can allow
    | for more consolidated commits when you have multiple users
    | making simultaneous content changes to your repository.
    |
    | Note: Not supported by default `sync` queue driver.
    |
    */

    'dispatch_delay' => env('STATAMIC_GIT_DISPATCH_DELAY', 0),

    /*
    |--------------------------------------------------------------------------
    | Git User
    |--------------------------------------------------------------------------
    |
    | The git user that will be used when committing changes. By default, it
    | will attempt to commit with the authenticated user's name and email
    | when possible, falling back to the below user when not available.
    |
    | https://statamic.dev/git-automation#git-user
    |
    */

    'use_authenticated' => true,

    'user' => [
        'name' => env('STATAMIC_GIT_USER_NAME', 'Spock'),
        'email' => env('STATAMIC_GIT_USER_EMAIL', 'spock@example.com'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracked Paths
    |--------------------------------------------------------------------------
    |
    | Define the tracked paths to be considered when staging changes. Default
    | stache and file locations are already set up for you, but feel free
    | to modify these paths to suit your storage config. Referencing
    | absolute paths to external repos is also completely valid.
    |
    */

    'paths' => [
        base_path('content'),
        base_path('users'),
        resource_path('blueprints'),
        resource_path('fieldsets'),
        resource_path('forms'),
        resource_path('users'),
        resource_path('preferences.yaml'),
        resource_path('sites.yaml'),
        storage_path('forms'),
        public_path('assets'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Git Binary
    |--------------------------------------------------------------------------
    |
    | By default, Statamic will try to use the "git" command, but you can set
    | an absolute path to the git binary if necessary for your environment.
    |
    */

    'binary' => env('STATAMIC_GIT_BINARY', 'git'),

    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    |
    | Define a list commands to be run when Statamic is ready to `git add`
    | and `git commit` your changes. These commands will be run once
    | per repo, attempting to consolidate commits where possible.
    |
    | https://statamic.dev/git-automation#customizing-commits
    |
    */

    'commands' => [
        '{{ git }} add {{ paths }}',
        '{{ git }} -c "user.name={{ name }}" -c "user.email={{ email }}" commit -m "{{ message }}"',
    ],

    /*
    |--------------------------------------------------------------------------
    | Push
    |--------------------------------------------------------------------------
    |
    | Determine whether `git push` should be run after the commands above
    | have finished. This is disabled by default, but can be enabled
    | globally, or per environment using the provided variable.
    |
    | https://statamic.dev/git-automation#pushing-changes
    |
    */

    'push' => env('STATAMIC_GIT_PUSH', false),

    /*
    |--------------------------------------------------------------------------
    | Ignored Events
    |--------------------------------------------------------------------------
    |
    | Statamic will listen on all `Saved` and `Deleted` events, as well
    | as any events registered by installed addons. If you wish to
    | ignore any specific events, you may reference them here.
    |
    */

    'ignored_events' => [
        // \Statamic\Events\UserSaved::class,
        // \Statamic\Events\UserDeleted::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Locale
    |--------------------------------------------------------------------------
    |
    | The locale to be used when translating commit messages, etc. By
    | default, the authenticated user's locale will be used, but
    | feel free to override this using the provided variable.
    |
    */

    'locale' => env('STATAMIC_GIT_LOCALE', null),

];
