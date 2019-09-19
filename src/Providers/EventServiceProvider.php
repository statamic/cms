<?php

namespace Statamic\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \Statamic\Events\DataIdCreated::class => [
            \Statamic\Stache\Listeners\SaveCreatedId::class
        ],
        'Form.submission.created' => [
            \Statamic\Forms\Listeners\SendEmails::class
        ],
        \Statamic\View\Events\ViewRendered::class => [
            \Statamic\View\Debugbar\AddVariables::class,
        ],
        \Illuminate\Auth\Events\Login::class => [
            \Statamic\Auth\SetLastLoginTimestamp::class
        ]
    ];

    protected $subscribe = [
        // \Statamic\Taxonomies\TermTracker::class, // TODO
        \Statamic\Listeners\GeneratePresetImageManipulations::class,
    ];
}
