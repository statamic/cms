<?php

namespace Statamic\Imaging;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use League\Glide\ServerFactory;
use Statamic\Facades\Config;
use Statamic\Facades\Image;
use Statamic\Imaging\ResponseFactory as LaravelResponseFactory;
use Statamic\Support\Str;

class GlideManager
{
    /**
     * Create glide server.
     *
     * @return \League\Glide\Server
     */
    public function server()
    {
        return ServerFactory::create([
            'source'   => base_path(), // this gets overriden on the fly by the image generator
            'cache'    => $this->cacheDisk()->getDriver(),
            'response' => new LaravelResponseFactory(app('request')),
            'driver'   => Config::get('statamic.assets.image_manipulation.driver'),
            'cache_with_file_extensions' => true,
            'presets' => Image::manipulationPresets(),
            'watermarks' => public_path(),
        ]);
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
}
