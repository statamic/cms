<?php

namespace Statamic\API\Endpoint;

use Statamic\Contracts\Data\Repositories\AssetContainerRepository;

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

    protected function repo()
    {
        return app(AssetContainerRepository::class);
    }
}
