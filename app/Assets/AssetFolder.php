<?php

namespace Statamic\Assets;

use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\Data\DataFolder;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Events\Data\AssetFolderDeleted;
use Statamic\API\AssetContainer as AssetContainerAPI;
use Statamic\Contracts\Assets\AssetFolder as Contract;

class AssetFolder implements Contract, Arrayable
{
    protected $container;
    protected $path;

    public function container($container = null)
    {
        if (func_num_args() === 0) {
            return $this->container;
        }

        $this->container = $container;

        return $this;
    }

    public function path($path = null)
    {
        if (func_num_args() === 0) {
            return $this->path;
        }

        $this->path = $path;

        return $this;
    }

    public function title($title = null)
    {
        if (func_num_args() === 0) {
            return $this->title ?? $this->computedTitle();
        }

        $this->title = $title;

        return $this;
    }

    protected function computedTitle()
    {
        return pathinfo($this->path(), PATHINFO_FILENAME);
    }

    public function disk()
    {
        return $this->container()->disk();
    }

    public function resolvedPath()
    {
        return Path::tidy($this->container()->diskPath() . '/' . $this->path());
    }

    public function count()
    {
        return $this->assets()->count();
    }

    public function assets($recursive = false)
    {
        return $this->container()->assets($this->path(), $recursive);
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
        $path = $this->path() . '/folder.yaml';

        if ($this->title === $this->computedTitle()) {
            $this->disk()->delete($path);
            return $this;
        }

        $this->disk()->makeDirectory($this->path());
        $this->disk()->put($path, YAML::dump(['title' => $this->title]));

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

        event(new AssetFolderDeleted($this->container(), $this->path(), $paths));

        return $this;
    }

    /**
     * @inheritdoc
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
     * Get the folder represented as an array
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'title' => $this->title(),
            'path' => $this->path(),
            'parent_path' => optional($this->parent())->path(),
        ];
    }
}
