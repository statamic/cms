<?php

namespace Statamic\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
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
            \Statamic\Entries\UpdateStructuredEntryOrderAndParent::class,
        ],
        \Statamic\Events\EntryBlueprintFound::class => [
            \Statamic\Entries\AddSiteColumnToBlueprint::class,
        ],
        \Statamic\Events\ResponseCreated::class => [
            \Statamic\Listeners\ClearState::class,
        ],
        \Illuminate\Foundation\Http\Events\RequestHandled::class => [
            \Statamic\Listeners\ClearState::class,
        ],
    ];

    protected $subscribe = [
        // \Statamic\Taxonomies\TermTracker::class, // TODO
        \Statamic\Listeners\ClearAssetGlideCache::class,
        \Statamic\Listeners\GeneratePresetImageManipulations::class,
        \Statamic\Listeners\UpdateAssetReferences::class,
        \Statamic\Listeners\UpdateTermReferences::class,
    ];

    public function register()
    {
        $this->booting(function () {
            foreach ($this->listen as $event => $listeners) {
                foreach (array_unique($listeners, SORT_REGULAR) as $listener) {
                    Event::listen($event, $listener);
                }
            }

            foreach ($this->subscribe as $subscriber) {
                Event::subscribe($subscriber);
            }
        });
    }

    public function boot()
    {
        Event::macro('forgetListener', function ($event, $handler) {
            $this->listeners[$event] = Arr::where($this->listeners[$event], function ($eventHandler) use ($handler) {
                return $eventHandler != $handler;
            });
        });
    }
}
