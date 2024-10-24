<?php

namespace Statamic\Imaging;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use League\Glide\ServerFactory;
use Statamic\Facades\Config;
use Statamic\Facades\Image;
use Statamic\Imaging\Manipulators\GlideManipulator;
use Statamic\Imaging\ResponseFactory as LaravelResponseFactory;
use Statamic\Support\Str;

/**
 * @deprecated Glide should be accessed through its manipulation class.
 * @see GlideManipulator
 */
class GlideManager
{
    private Closure $customHashCallable;

    /**
     * Create glide server.
     *
     * @param  array  $config  Config overrides
     * @return \League\Glide\Server
     */
    public function server(array $config = [])
    {
        $server = ServerFactory::create(array_merge([
            'source' => base_path(), // this gets overridden on the fly by the image generator
            'cache' => $this->cacheDisk()->getDriver(),
            'response' => new LaravelResponseFactory(app('request')),
            'driver' => Config::get('statamic.assets.image_manipulation.driver'),
            'cache_with_file_extensions' => true,
            'presets' => Image::manipulationPresets(),
            'watermarks' => public_path(),
        ], $config));

        $server->setCachePathCallable($this->getCachePathCallable());

        return $server;
    }

    public function cacheDisk()
    {
        return $this->wantsCustomFilesystem()
            ? $this->customCacheFilesystem()
            : $this->localCacheFilesystem();
    }

    private function wantsCustomFilesystem()
    {
        return is_string(Config::get('statamic.assets.image_manipulation.cache'));
    }

    private function localCacheFilesystem()
    {
        if (! $root = $this->cachePath()) {
            throw new \Exception('Image manipulation cache path is not defined.');
        }

        return Storage::build([
            'driver' => 'local',
            'root' => $root,
            'visibility' => 'public',
        ]);
    }

    private function customCacheFilesystem()
    {
        return Storage::disk(Config::get('statamic.assets.image_manipulation.cache'));
    }

    /**
     * Get glide cache path.
     *
     * @return string
     */
    private function cachePath()
    {
        return $this->shouldServeDirectly()
            ? Config::get('statamic.assets.image_manipulation.cache_path')
            : storage_path('statamic/glide');
    }

    public function shouldServeDirectly()
    {
        return (bool) Config::get('statamic.assets.image_manipulation.cache');
    }

    public function shouldServeByHttp()
    {
        return ! $this->shouldServeDirectly();
    }

    public function route()
    {
        return Config::get('statamic.assets.image_manipulation.route');
    }

    public function url()
    {
        $url = $this->wantsCustomFilesystem()
            ? self::cacheDisk()->url('/')
            : Str::start(self::route(), '/');

        return Str::removeRight($url, '/');
    }

    public function cacheStore()
    {
        if (! config()->has('cache.stores.glide')) {
            config(['cache.stores.glide' => [
                'driver' => 'file',
                'path' => storage_path('framework/cache/glide'),
            ]]);
        }

        return Cache::store('glide');
    }

    public function clearAsset($asset)
    {
        $pathPrefix = ImageGenerator::assetCachePathPrefix($asset);
        $manifestKey = ImageGenerator::assetCacheManifestKey($asset);

        // Delete generated glide cache for asset.
        // Make sure to use the default cache path when clearing the cache
        tap($this->server(), function ($server) use ($pathPrefix, $asset) {
            $customCallable = $server->getCachePathCallable();
            $server->setCachePathCallable(null);
            $server->deleteCache($pathPrefix.'/'.$asset->path());
            $server->setCachePathCallable($customCallable);
        });

        // Use manifest to clear each manipulation key from cache store.
        collect($this->cacheStore()->get($manifestKey, []))->each(function ($manipulationKey) {
            $this->cacheStore()->forget($manipulationKey);
        });

        // Clear manifest itself from cache store.
        $this->cacheStore()->forget($manifestKey);
    }

    public function normalizeParameters($params)
    {
        $legend = [
            'background' => 'bg',
            'brightness' => 'bri',
            'contrast' => 'con',
            'filter' => 'filt',
            'format' => 'fm',
            'gamma' => 'gam',
            'height' => 'h',
            'orientation' => 'or',
            'pixelate' => 'pixel',
            'quality' => 'q',
            'sharpen' => 'sharp',
            'width' => 'w',
            'watermark' => 'mark',
        ];

        return collect($params)->mapWithKeys(function ($value, $param) use ($legend) {
            return [$legend[$param] ?? $param => $value];
        })->all();
    }

    private function getCachePathCallable()
    {
        $hashCallable = $this->getHashCallable();

        return function ($path, $params) use ($hashCallable) {
            $sourcePath = $this->getSourcePath($path);

            if ($this->sourcePathPrefix) {
                $sourcePath = substr($sourcePath, strlen($this->sourcePathPrefix) + 1);
            }

            $params = $this->getAllParams($params);
            unset($params['s'], $params['p']);
            ksort($params);

            $ext = $params['fm'] ?? pathinfo($path, PATHINFO_EXTENSION);
            $ext = $ext === 'pjpg' ? 'jpg' : $ext;
            $ext = $ext ? ".$ext" : '';

            return vsprintf('%s/%s/%s/%s%s', [
                $this->cachePathPrefix,
                $sourcePath,
                $hashCallable($sourcePath, $params),
                pathinfo($path, PATHINFO_FILENAME),
                $ext,
            ]);
        };
    }

    private function getHashCallable()
    {
        return $this->customHashCallable ?? function (string $source, array $params) {
            return md5($source.'?'.http_build_query($params));
        };
    }

    public function generateHashUsing(Closure $callback)
    {
        $this->customHashCallable = $callback;
    }
}
