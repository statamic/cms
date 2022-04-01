<?php

namespace Statamic\Imaging;

use Illuminate\Support\Facades\Storage;
use League\Glide\ServerFactory;
use Statamic\Facades\Config;
use Statamic\Facades\Image;
use Statamic\Imaging\ResponseFactory as LaravelResponseFactory;
use Statamic\Support\Str;

class GlideServer
{
    /**
     * Create glide server.
     *
     * @return \League\Glide\Server
     */
    public function create()
    {
        return ServerFactory::create([
            'source'   => base_path(), // this gets overriden on the fly by the image generator
            'cache'    => $this->cacheDisk()->getDriver(),
            'response' => new LaravelResponseFactory(app('request')),
            'driver'   => Config::get('statamic.assets.image_manipulation.driver'),
            'cache_with_file_extensions' => true,
            'presets' => Image::manipulationPresets(),
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
        return Storage::build([
            'driver' => 'local',
            'root' => $this->cachePath(),
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

    public function url()
    {
        $url = $this->wantsCustomFilesystem()
            ? self::cacheDisk()->url('/')
            : Str::start(Config::get('statamic.assets.image_manipulation.route'), '/');

        return Str::removeRight($url, '/');
    }
}
