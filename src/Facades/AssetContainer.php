<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Assets\AssetContainerRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static null|\Statamic\Contracts\Assets\AssetContainer find($id)
 * @method static null|\Statamic\Contracts\Assets\AssetContainer findByHandle(string $handle)
 * @method static \Statamic\Contracts\Assets\AssetContainer findOrFail($id)
 * @method static \Statamic\Contracts\Assets\AssetContainer make(string $handle = null)
 *
 * @see \Statamic\Assets\AssetContainerRepository
 */
class AssetContainer extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AssetContainerRepository::class;
    }
}
