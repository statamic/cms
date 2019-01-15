<?php

namespace Tests;

trait PreventSavingStacheItemsToDisk
{
    protected function preventSavingStacheItemsToDisk()
    {
        $stores = collect([
            NonSavingCollectionsStore::class,
            NonSavingEntriesStore::class,
            NonSavingGlobalsStore::class,
        ])->map(function ($class) {
            return app($class)->directory(__DIR__.'/__fixtures__/dev-null');
        });

        $this->app['stache']->registerStores($stores->all());
    }
}

class NonSavingCollectionsStore extends \Statamic\Stache\Stores\CollectionsStore
{
    public function save(\Statamic\Contracts\Data\Entries\Collection $collection) { }
}

class NonSavingEntriesStore extends \Statamic\Stache\Stores\EntriesStore
{
    public function save($entry) { }
}

class NonSavingGlobalsStore extends \Statamic\Stache\Stores\GlobalsStore
{
    public function save(\Statamic\Contracts\Data\Globals\GlobalSet $globals) { }
}
