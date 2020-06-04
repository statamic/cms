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
    | https://statamic.dev/git-integration
    |
    */

    'enabled' => env('STATAMIC_GIT_ENABLED', false),

    /*
    |--------------------------------------------------------------------------
    | Automatically Run
    |--------------------------------------------------------------------------
    |
    | By default, git commands will be run automatically after `DataSaved`
    | events are fired. If you prefer users to manually trigger the git
    | commands, set this to `false` and your users will be presented
    | with the relevant GUI to commit the changes as they see fit.
    |
    */

    'automatic' => env('STATAMIC_GIT_AUTOMATIC', true),

    /*
    |--------------------------------------------------------------------------
    | Git User
    |--------------------------------------------------------------------------
    |
    | The git user that will be used when committing changes to your repo.
    | By default, the currently authenticated user name and email will
    | be used. However, this can be overridden to something global.
    |
    */

    'user' => [
        'name' => env('STATAMIC_GIT_USER_NAME', '{{ current_user_name }}'),
        'email' => env('STATAMIC_GIT_USER_EMAIL', '{{ current_user_email }}'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Tracked Paths
    |--------------------------------------------------------------------------
    |
    | Define the tracked paths to be considered when staging changes. Default
    | stache and file locations are already set up for you, but feel free
    | to modify these paths to suit your storage config. Absolute paths
    | are valid when referencing content stored in external repos.
    |
    */

    'paths' => [
        'content',
        'users',
        'resources/users',
        'public/assets',
    ],

    /*
    |--------------------------------------------------------------------------
    | Commands
    |--------------------------------------------------------------------------
    |
    | Define a list commands to be run when Statamic is ready to `git add`
    | and `git commit` your changes. These commands will be run once
    | per repo, attempting to consolidate commits where possible.
    |
    */

    'commands' => [
        'cd {{ git_root }}',
        'git add {{ paths }}',
        'git commit -m "{{ message }}" -c "user.name={{ name }}" -c "user.email={{ email }}"',
    ],

    /*
    |--------------------------------------------------------------------------
    | Push
    |--------------------------------------------------------------------------
    |
    | Determine whether `git push` should be run after the commands above
    | have finished.  This is disabled by default, but can be enabled
    | globally, or per environment using the provided variable.
    |
    */

    'push' => env('STATAMIC_GIT_PUSH', false),

    /*
    |--------------------------------------------------------------------------
    | Ignored Events
    |--------------------------------------------------------------------------
    |
    | Statamic will listen on all `DataSaved` compatible events, as well as
    | any `DataSaved` events registered by installed addons. If you wish
    | to ignore any specific events, you may reference them here.
    |
    */

    'ignored_events' => [
        // \Statamic\Events\UserSaved::class,
    ],

];
