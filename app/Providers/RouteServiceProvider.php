<?php

namespace Statamic\Providers;

use Statamic\API\Site;
use Statamic\API\Entry;
use Statamic\API\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->bindEntries();
        $this->bindCollections();
        $this->bindSites();
    }

    protected function bindEntries()
    {
        Route::bind('entry', function ($entry, $route) {
            abort_if(
                ! ($entry = Entry::find($entry))
                || $entry->collection() !== $route->parameter('collection')
                || ! Site::get($site = $route->parameter('site'))
                || ! $entry->collection()->sites()->contains($site)
            , 404);

            return $entry->inOrClone($site);
        });
    }

    protected function bindCollections()
    {
        Route::bind('collection', function ($collection) {
            abort_if(! $collection = Collection::whereHandle($collection), 404);
            return $collection;
        });
    }

    protected function bindSites()
    {
        Route::bind('site', function ($site) {
            abort_if(! $site = Site::get($site), 404);
            return $site;
        });
    }
}
