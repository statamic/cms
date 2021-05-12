<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\AssetContainer;

class AssetsStore extends AggregateStore
{
    protected $childStore = ContainerAssetsStore::class;

    public function key()
    {
        return 'assets';
    }

    public function discoverStores()
    {
        return AssetContainer::all()->map->handle()->map(function ($handle) {
            return $this->store($handle);
        });
    }
}
