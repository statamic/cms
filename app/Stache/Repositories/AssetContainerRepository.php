<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Illuminate\Support\Collection;
use Statamic\Contracts\Assets\AssetContainer;
use Illuminate\Support\Collection as IlluminateCollection;
use Statamic\Contracts\Data\Repositories\AssetContainerRepository as RepositoryContract;

class AssetContainerRepository implements RepositoryContract
{
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->store = $stache->store('asset-containers');
    }

    public function all(): Collection
    {
        return $this->store->getItems();
    }

    public function findByHandle(string $handle): ?AssetContainer
    {
        return $this->store->getItem($handle);
    }

    public function create()
    {
        return app(AssetContainer::class);
    }

    public function save(AssetContainer $container)
    {
        $this->store->setItem($container->handle(), $container);

        $this->store->save($container);
    }
}
