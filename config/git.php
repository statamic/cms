<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Automatic Git Integration
    |--------------------------------------------------------------------------
    |
    | Whether automatic git integration should be enabled. This feature
    | assumes that git is already installed and accessible by your
    | PHP process' server user. For more info, see the docs at:
    |
    | https://statamic.dev/git-integration
    |
    */

    'enabled' => env('STATAMIC_GIT_ENABLED', false),

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
    | Commands
    |--------------------------------------------------------------------------
    |
    | Define the commands to be run when `DataSaved` events are fired. By
    | default this will `git add` your changes, and `git commit` them
    | to the git repository relative to your working directory.
    |
    */

    'commands' => [
        'cd {{ cwd }}',
        'git add {{ path }}',
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
