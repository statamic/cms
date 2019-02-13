<?php

namespace Tests;

trait PreventSavingStacheItemsToDisk
{
    protected $fakeStacheDirectory = __DIR__.'/__fixtures__/dev-null';

    protected function preventSavingStacheItemsToDisk()
    {
        $stores = collect([
            NonSavingCollectionsStore::class,
            NonSavingEntriesStore::class,
            NonSavingGlobalsStore::class,
            NonSavingAssetContainersStore::class,
            NonSavingUsersStore::class,
        ])->map(function ($class) {
            return app($class)->directory($this->fakeStacheDirectory);
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
    public function save($globals) { }
}

class NonSavingAssetContainersStore extends \Statamic\Stache\Stores\AssetContainersStore
{
    public function save($container) { }
}

class NonSavingUsersStore extends \Statamic\Stache\Stores\UsersStore
{
    public function save($container) { }
}
