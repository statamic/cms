<?php

namespace Statamic\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Statamic\Events\Subscriber;
use Statamic\Support\Arr;

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
        \Statamic\Events\ResponseCreated::class => [
            \Statamic\View\State\ClearState::class,
        ],
        \Illuminate\Foundation\Http\Events\RequestHandled::class => [
            \Statamic\View\State\ClearState::class,
        ],
    ];

    protected $subscribe = [
        // \Statamic\Taxonomies\TermTracker::class, // TODO
        \Statamic\Listeners\ClearAssetGlideCache::class,
        \Statamic\Listeners\GeneratePresetImageManipulations::class,
        \Statamic\Listeners\UpdateAssetReferences::class,
        \Statamic\Listeners\UpdateTermReferences::class,
    ];

    public function boot()
    {
        Event::macro('forgetListener', function ($event, $handler) {
            $this->listeners[$event] = Arr::where($this->listeners[$event], function ($eventHandler) use ($handler) {
                return Subscriber::normalizeRegisteredListener($eventHandler) != $handler;
            });
        });
    }
}
