<?php

namespace Statamic\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        \Statamic\View\Events\ViewRendered::class => [
            \Statamic\View\Debugbar\AddVariables::class,
            \Statamic\View\Debugbar\AddRequestMessage::class,
        ],
        \Illuminate\Auth\Events\Login::class => [
            \Statamic\Auth\SetLastLoginTimestamp::class,
        ],
        \Statamic\Events\CollectionStructureTreeSaved::class => [
            \Statamic\Entries\UpdateStructuredEntryUris::class,
        ],
    ];

    protected $subscribe = [
        // \Statamic\Taxonomies\TermTracker::class, // TODO
        \Statamic\Listeners\GeneratePresetImageManipulations::class,
    ];
}
