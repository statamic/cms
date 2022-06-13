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
        \Statamic\Events\CollectionTreeSaved::class => [
            \Statamic\Entries\UpdateStructuredEntryUris::class,
            \Statamic\Entries\UpdateStructuredEntryOrder::class,
        ],
        \Statamic\Events\EntryBlueprintFound::class => [
            \Statamic\Entries\AddSiteColumnToBlueprint::class,
        ],
    ];

    protected $subscribe = [
        // \Statamic\Taxonomies\TermTracker::class, // TODO
        \Statamic\Listeners\GeneratePresetImageManipulations::class,
        \Statamic\Listeners\UpdateAssetReferences::class,
        \Statamic\Listeners\UpdateTermReferences::class,
    ];
}
