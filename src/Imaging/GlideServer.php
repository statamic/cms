<?php

namespace Statamic\Imaging;

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
            'cache'    => $this->cachePath(),
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
}
