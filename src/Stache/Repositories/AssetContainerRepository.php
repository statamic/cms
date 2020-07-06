<?php

namespace Statamic\Stache\Repositories;

use Illuminate\Support\Collection;
use Statamic\Contracts\Assets\AssetContainer;
use Statamic\Contracts\Assets\AssetContainerRepository as RepositoryContract;
use Statamic\Stache\Stache;

class AssetContainerRepository implements RepositoryContract
{
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->store = $stache->store('asset-containers');
    }

    public function all(): Collection
    {
        $keys = $this->store->paths()->keys();

        return $this->store->getItems($keys);
    }

    public function find($id): ?AssetContainer
    {
        return $this->findByHandle($id);
    }

    public function findByHandle(string $handle): ?AssetContainer
    {
        return $this->store->getItem($handle);
    }

    public function make(string $handle = null): AssetContainer
    {
        return app(AssetContainer::class)->handle($handle);
    }

    public function save(AssetContainer $container)
    {
        $this->store->save($container);
    }

    public function delete($container)
    {
        $this->store->delete($container);
    }

    public static function bindings(): array
    {
        return [
            AssetContainer::class => \Statamic\Assets\AssetContainer::class,
        ];
    }
}
