<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Assets\AssetRepository;

/**
 * @method static mixed all()
 * @method static mixed whereContainer(string $container)
 * @method static mixed whereFolder(string $folder, string $container)
 * @method static mixed find(string $asset)
 * @method static mixed findByUrl(string $url)
 * @method static mixed findById(string $id)
 * @method static mixed findByPath(string $path)
 * @method static mixed make()
 * @method static mixed query()
 * @method static mixed save($asset)
 *
 * @see \Statamic\Contracts\Assets\AssetRepository
 */
class Asset extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AssetRepository::class;
    }
}
