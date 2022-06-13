<?php

namespace Statamic\Assets;

use Illuminate\Support\Facades\Cache;
use League\Flysystem\Util;
use Statamic\Support\Str;

class AssetContainerContents
{
    protected $container;
    protected $files;
    protected $filteredFiles;
    protected $filteredDirectories;

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

    public function filteredFilesIn($folder, $recursive)
    {
        if (isset($this->filteredFiles[$key = $folder.($recursive ? '-recursive' : '')])) {
            return $this->filteredFiles[$key];
        }

        $files = $this->files();

        // Filter by folder and recursiveness. But don't bother if we're
        // requesting the root recursively as it's already that way.
        if ($folder === '/' && $recursive) {
            //
        } else {
            $files = $files->filter(function ($file) use ($folder, $recursive) {
                $dir = $file['dirname'] ?: '/';

                return $recursive ? Str::startsWith($dir, $folder) : $dir == $folder;
            });
        }

        // Get rid of files we never want to show up.
        $files = $files->reject(function ($file, $path) {
            return Str::startsWith($path, '.meta/')
                || Str::contains($path, '/.meta/')
                || Str::endsWith($path, ['.DS_Store', '.gitkeep', '.gitignore']);
        });

        return $this->filteredFiles[$key] = $files;
    }

    public function filteredDirectoriesIn($folder, $recursive)
    {
        if (isset($this->filteredDirectories[$key = $folder.($recursive ? '-recursive' : '')])) {
            return $this->filteredDirectories[$key];
        }

        $files = $this->directories();

        // Filter by folder and recursiveness. But don't bother if we're
        // requesting the root recursively as it's already that way.
        if ($folder === '/' && $recursive) {
            //
        } else {
            $files = $files->filter(function ($file) use ($folder, $recursive) {
                $dir = $file['dirname'] ?: '/';

                return $recursive ? Str::startsWith($dir, $folder) : $dir == $folder;
            });
        }

        $files = $files->reject(function ($file) {
            return $file['basename'] == '.meta';
        });

        return $this->filteredDirectories[$key] = $files;
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

        $this->filteredFiles = null;
        $this->filteredDirectories = null;

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

            $this->all()->put($path, $metadata + Util::pathinfo($path));

            $this->filteredFiles = null;
            $this->filteredDirectories = null;
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
