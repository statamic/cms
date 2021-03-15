<?php

namespace Statamic\Assets;

use Illuminate\Support\Facades\Cache;
use League\Flysystem\Util;

class AssetContainerContents
{
    protected $container;
    protected $files;

    public function __construct($container)
    {
        $this->container = $container;
    }

    public function all()
    {
        return $this->files = $this->files
            ?? Cache::remember($this->key(), $this->ttl(), function () {
                // Use Flysystem directly because it gives us type, timestamps, dirname
                // and will let us perform more efficient filtering and caching.
                $files = $this->filesystem()->listContents('/', true);

                return collect($files)->keyBy('path');
            });
    }

    public function cached()
    {
        return Cache::get($this->key());
    }

    public function files()
    {
        return $this->all()->where('type', 'file');
    }

    public function directories()
    {
        return $this->all()->where('type', 'dir');
    }

    private function filesystem()
    {
        return $this->container->disk()->filesystem()->getDriver();
    }

    public function save()
    {
        Cache::put($this->key(), $this->all(), $this->ttl());
    }

    public function forget($path)
    {
        $this->files = $this->all()->forget($path);

        return $this;
    }

    public function add($path)
    {
        try {
            // If the file doesn't exist, this will either throw an exception or return
            // false depending on the adapter and whether or not asserts are enabled.
            if (! $metadata = $this->filesystem()->getMetadata($path)) {
                return $this;
            }

            // Add parent directories
            if (($dir = dirname($path)) !== '.') {
                $this->add($dir);
            }

            $this->files->put($path, $metadata + Util::pathinfo($path));
        } finally {
            return $this;
        }
    }

    private function key()
    {
        return 'asset-list-contents-'.$this->container->handle();
    }

    private function ttl()
    {
        return config('statamic.stache.watcher') ? 0 : null;
    }
}
