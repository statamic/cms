<?php

namespace Statamic\Imaging;

use League\Glide\ServerFactory;
use Statamic\Facades\Config;
use Statamic\Facades\Image;
use Illuminate\Support\Facades\Storage;
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
            'cache'    => $this->cacheDisk(),
            'response' => new LaravelResponseFactory(app('request')),
            'driver'   => Config::get('statamic.assets.image_manipulation.driver'),
            'cache_with_file_extensions' => true,
            'presets' => $this->presets(),
        ]);
    }

    /**
     * Get glide cache.
     *
     * @return string|\League\Flysystem\Filesystem
     */
    public function cacheDisk()
    {
        return Config::get('statamic.assets.image_manipulation.cache')
            ? Storage::disk(Config::get('statamic.assets.image_manipulation.cache_disk'))->getDriver()
            : storage_path('statamic/glide');
    }

    /**
     * Get glide presets.
     *
     * @return array
     */
    private function presets()
    {
        $presets = Config::getImageManipulationPresets();

        if (config('statamic.cp.enabled')) {
            $presets = array_merge($presets, Image::getCpImageManipulationPresets());
        }

        return $presets;
    }
}
