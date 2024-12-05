<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Imaging\GlideManager;

/**
 * @method static \League\Glide\Server server(array $config = [])
 * @method static \Illuminate\Contracts\Filesystem\Filesystem cacheDisk()
 * @method static bool shouldServeDirectly()
 * @method static bool shouldServeByHttp()
 * @method static string route()
 * @method static string url()
 * @method static \Illuminate\Contracts\Cache\Repository cacheStore()
 * @method static void clearAsset($asset)
 * @method static array normalizeParameters($params)
 * @method static void generateHashUsing(\Closure $callback)
 *
 * @see \Statamic\Imaging\GlideManager
 */
class Glide extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GlideManager::class;
    }
}
