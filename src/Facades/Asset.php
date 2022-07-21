<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Assets\AssetCollection;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetRepository;
use Statamic\Contracts\Assets\QueryBuilder;

/**
 * @method static AssetCollection all()
 * @method static AssetCollection whereContainer(string $container)
 * @method static AssetCollection whereFolder(string $folder, string $container)
 * @method static AssetContract|null find(string $asset)
 * @method static AssetContract|null findByUrl(string $url)
 * @method static AssetContract|null findById(string $id)
 * @method static AssetContract|null findByPath(string $path)
 * @method static AssetContract make()
 * @method static QueryBuilder query()
 * @method static void save($asset)
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
