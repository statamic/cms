<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Assets\AssetRepository;

/**
 * @method static all()
 * @method static whereContainer(string $container)
 * @method static whereFolder(string $folder, string $container)
 * @method static find(string $asset)
 * @method static findByUrl(string $url)
 * @method static findById(string $id)
 * @method static findByPath(string $path)
 * @method static make()
 * @method static query()
 * @method static save($asset)
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
