<?php

namespace Statamic\Assets;

use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Assets\AssetFolder as Contract;
use Statamic\Events\AssetFolderDeleted;
use Statamic\Events\AssetFolderSaved;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Path;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class AssetFolder implements Contract, Arrayable
{
    use FluentlyGetsAndSets;

    protected $container;
    protected $path;

    public static function find($reference)
    {
        [$container, $path] = explode('::', $reference);

        return (new static)
            ->container(AssetContainer::find($container))
            ->path($path);
    }

    public function container($container = null)
    {
        return $this->fluentlyGetOrSet('container')->args(func_get_args());
    }

    public function path($path = null)
    {
        return $this->fluentlyGetOrSet('path')->args(func_get_args());
    }

    public function basename()
    {
        return pathinfo($this->path(), PATHINFO_BASENAME);
    }

    public function title()
    {
        return pathinfo($this->path(), PATHINFO_FILENAME);
    }

    public function disk()
    {
        return $this->container()->disk();
    }

    public function resolvedPath()
    {
        return Path::tidy($this->container()->diskPath().'/'.$this->path());
    }

    public function count()
    {
        return $this->assets()->count();
    }

    public function assets($recursive = false)
    {
        return $this->container()->assets($this->path(), $recursive);
    }

    public function queryAssets()
    {
        return $this->container()->queryAssets()->where('folder', $this->path);
    }

    public function assetFolders()
    {
        return $this->container()->assetFolders($this->path, false);
    }

    public function lastModified()
    {
        $date = null;

        foreach ($this->assets() as $asset) {
            $modified = $asset->lastModified();

            if ($date) {
                if ($modified->gt($date)) {
                    $date = $modified;
                }
            } else {
                $date = $modified;
            }
        }

        return $date;
    }

    public function save()
    {
        $this->disk()->makeDirectory($this->path());

        AssetFolderSaved::dispatch($this);

        return $this;
    }

    public function delete()
    {
        $paths = [];

        // Iterate over all the assets in the folder, recursively.
        foreach ($this->assets(true) as $asset) {
            // Keep track of the paths for the event
            $paths[] = $asset->path();

            // Delete the asset. It'll remove its own data.
            $asset->delete();
        }

        // Delete the actual folder that'll be leftover. It'll include any empty subfolders.
        $this->disk()->delete($this->path());

        $cache = $this->container->contents();
        $cache->directories()->keys()->filter(function ($path) {
            return Str::startsWith($path, $this->path());
        })->each(function ($path) use ($cache) {
            $cache->forget($path);
        });
        $cache->save();

        AssetFolderDeleted::dispatch($this);

        return $this;
    }

    /**
     * Rename the folder.
     *
     * @param string $filename
     * @return AssetFolder
     * @throws \Exception
     */
    public function rename($name)
    {
        return $this->move(optional($this->parent())->path(), $name);
    }

    /**
     * Move the folder to a different location.
     *
     * @param string      $parent   The destination folder relative to the container.
     * @param string|null $name     The new folder name, if renaming.
     * @return AssetFolder
     * @throws \Exception
     */
    public function move($parent, $name = null)
    {
        if (Str::startsWith($parent, $this->path())) {
            throw new \Exception(__('Folder cannot be moved to its own subfolder.'));
        }

        $name = $name ?: $this->basename();
        $oldPath = $this->path();
        $newPath = Str::removeLeft(Path::tidy($parent.'/'.$name), '/');

        if ($oldPath === $newPath) {
            return $this;
        }

        $newPath = $this->ensureUniquePath($newPath);

        $folder = $this->container->assetFolder($newPath)->save();
        $this->container()->assets($oldPath, true)->each->move($newPath);
        $this->delete();

        return $folder;
    }

    /**
     * {@inheritdoc}
     */
    public function parent()
    {
        if ($this->path() === '/') {
            return null;
        }

        $path = Path::popLastSegment($this->path());
        $path = ($path === '') ? '/' : $path;

        return $this->container()->assetFolder($path);
    }

    /**
     * Get the folder represented as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'title' => $this->title(),
            'path' => $this->path(),
            'parent_path' => optional($this->parent())->path(),
            'basename' => $this->basename(),
        ];
    }

    protected function ensureUniquePath($path, $count = 0)
    {
        $suffix = $count ? "-$count" : '';
        $newPath = Str::removeLeft($path.$suffix, '/');

        if ($this->disk()->exists($newPath)) {
            return $this->ensureUniquePath($path, $count + 1);
        }

        return $newPath;
    }
}
