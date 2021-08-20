<?php

namespace Statamic\Imaging;

use Illuminate\Support\Facades\Storage;
use League\Glide\ServerFactory;
use Statamic\Facades\Config;
use Statamic\Facades\Image;
use Statamic\Imaging\ResponseFactory as LaravelResponseFactory;

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
            'cache'    => $this->cacheDisk() ?? $this->cachePath(),
            'response' => new LaravelResponseFactory(app('request')),
            'driver'   => Config::get('statamic.assets.image_manipulation.driver'),
            'cache_with_file_extensions' => true,
            'presets' => Image::manipulationPresets(),
        ]);
    }

    /**
     * Get glide cache path.
     *
     * @return string
     */
    public function cachePath()
    {
        return Config::get('statamic.assets.image_manipulation.cache')
            ? Config::get('statamic.assets.image_manipulation.cache_path')
            : storage_path('statamic/glide');
    }

    /**
     * Get glide cache file system.
     *
     * @return \League\Flysystem\FilesystemInterface|null
     */
    public function cacheDisk()
    {
        if (! Config::get('statamic.assets.image_manipulation.cache')) {
            return null;
        }

        if (! $disk = Config::get('statamic.assets.image_manipulation.cache_disk')) {
            return null;
        }

        return Storage::disk($disk)->getDriver();
    }
}
