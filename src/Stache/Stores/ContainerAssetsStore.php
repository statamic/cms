<?php

namespace Statamic\Stache\Stores;

use Illuminate\Support\Facades\Cache;
use Statamic\Facades\AssetContainer;
use Statamic\Statamic;
use Statamic\Support\Str;

class ContainerAssetsStore extends ChildStore
{
    private $container;

    private function container()
    {
        return $this->container = $this->container ?? AssetContainer::findByHandle($this->childKey());
    }

    public function handleFileChanges()
    {
        // We only want to act on any file changes one time per store.
        if ($this->fileChangesHandled) {
            return;
        }

        $this->fileChangesHandled = true;

        if (! config('statamic.stache.watcher')) {
            return;
        }

        $this->clear();
    }

    public function getItem($key)
    {
        $path = Str::after($key, '::');

        $asset = $this->container()->makeAsset($path);

        return $asset;
    }

    public function getItemsFromFiles()
    {
        if ($this->shouldCacheFileItems && $this->fileItems) {
            return $this->fileItems;
        }

        return $this->fileItems = $this->paths()->map(function ($path) {
            return $this->getItem($path);
        });
    }

    public function paths()
    {
        if ($this->paths && ! Statamic::isWorker()) {
            return $this->paths;
        }

        if ($paths = Cache::get($this->pathsCacheKey())) {
            return $this->paths = collect($paths);
        }

        $container = $this->container();
        $handle = $container->handle();

        $files = $this->getFiles();

        $paths = $files->mapWithKeys(function ($file) use ($handle) {
            $path = $file['path'];

            return ["$handle::$path" => $path];
        });

        $this->cachePaths($paths);

        return $paths;
    }

    private function getFiles()
    {
        return $this->container()->listContents()->reject(function ($file) {
            return $file['type'] !== 'file'
                || $file['path'] === ''
                || $file['dirname'] === '.meta'
                || Str::contains($file['path'], '/.meta/')
                || in_array($file['basename'], ['.gitignore', '.gitkeep', '.DS_Store']);
        });
    }

    protected function writeItemToDisk($item)
    {
        //
    }

    protected function deleteItemFromDisk($item)
    {
        //
    }
}
