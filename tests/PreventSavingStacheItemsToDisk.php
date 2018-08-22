<?php

namespace Tests;

use Statamic\Stache\Stores\CollectionsStore;

trait PreventSavingStacheItemsToDisk
{
    protected function preventSavingStacheItemsToDisk()
    {
        $stores = collect([
            NonSavingCollectionsStore::class,
        ])->map(function ($class) {
            return app($class)->directory(__DIR__); // Directory is irrelevant but it needs one.
        });

        $this->app['stache']->registerStores($stores->all());
    }
}

class NonSavingCollectionsStore extends CollectionsStore
{
    public function save(\Statamic\Contracts\Data\Entries\Collection $collection) { }
}
