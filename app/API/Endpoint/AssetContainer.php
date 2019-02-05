<?php

namespace Statamic\API\Endpoint;

use Statamic\Contracts\Data\Repositories\AssetContainerRepository;
use Statamic\Contracts\Assets\AssetContainer as ContainerContract;

class AssetContainer
{
    /**
     * Get all asset containers
     *
     * @return \Illuminate\Support\Collection
     */
    public function all()
    {
        return $this->repo()->all();
    }

    /**
     * Get an asset container by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function find($id)
    {
        return $this->repo()->findByHandle($id);
    }

    /**
     * Get an asset container by path
     *
     * @param string $path
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function wherePath($path)
    {
        return self::all()->filter(function ($container) use ($path) {
            return $container->path() == $path;
        })->first();
    }

    /**
     * Create an asset container
     *
     * @param string|null $driver
     * @return \Statamic\Contracts\Assets\AssetContainer
     */
    public function create($driver = null)
    {
        return $this->repo()->create($driver);
    }

    public function make($handle = null)
    {
        $collection = app(ContainerContract::class);

        if ($handle) {
            $collection->handle($handle);
        }

        return $collection;
    }

    public function save(ContainerContract $container)
    {
        $this->repo()->save($container);

        // AssetContainerSaved::dispatch($container); // TODO
    }

    protected function repo()
    {
        return app(AssetContainerRepository::class);
    }
}
