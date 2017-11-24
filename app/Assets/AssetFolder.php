<?php

namespace Statamic\Assets;

use Statamic\API\Path;
use Statamic\API\YAML;
use Statamic\Data\DataFolder;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Events\Data\AssetFolderDeleted;
use Statamic\API\AssetContainer as AssetContainerAPI;
use Statamic\Contracts\Assets\AssetFolder as AssetFolderContract;

class AssetFolder extends DataFolder implements AssetFolderContract, Arrayable
{
    /**
     * @var string
     */
    protected $container;

    /**
     * Create a new asset folder instance
     *
     * @param string     $path
     * @param array|null $data
     */
    public function __construct($container_id, $path, $data = [])
    {
        $this->container = $container_id;
        $this->path   = $path;
        $this->data   = $data;
    }

    /**
     * @inheritdoc
     */
    public function disk($type = 'folder')
    {
        return $this->container()->disk($type);
    }

    /**
     * @inheritdoc
     */
    public function resolvedPath()
    {
        return Path::tidy($this->container()->resolvedPath() . '/' . $this->path());
    }

    /**
     * @inheritdoc
     */
    public function computedTitle()
    {
        return pathinfo($this->path())['filename'];
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->assets()->count();
    }

    /**
     * @inheritdoc
     */
    public function assets($recursive = false)
    {
        return $this->container()->assets($this->path(), $recursive);
    }

    /**
     * @inheritdoc
     */
    public function lastModified()
    {
        $date = null;

        foreach ($this->assets() as $asset) {
            $modified = $asset->getLastModified();

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

    /**
     * @inheritdoc
     */
    public function save()
    {
        $data = $this->data();

        // If there's a title set, and it's the same as what the default would be
        // (ie. the folder name) then we'll just remove it. It's not needed.
        if ($title = array_get($data, 'title')) {
            if ($title === $this->computedTitle()) {
                unset($data['title']);
            }
        }

        // Make sure there's a folder
        $this->disk('folder')->make($this->path());

        $path = $this->path() . '/folder.yaml';

        // If there's no data to be saved, and there's already an existing folder.yaml,
        // we'll delete the file now. There's no reason for it to be hanging around.
        if (empty($data) && $this->disk('file')->exists($path)) {
            $this->disk('file')->delete($path);
        }

        // Only bother saving a file if there's data to save.
        if (! empty($data)) {
            $this->disk('file')->put($path, YAML::dump($data));
        }

        event('assetfolder.saved', $this);
    }

    /**
     * @inheritdoc
     */
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
        $this->disk('folder')->delete($this->path());

        event(new AssetFolderDeleted($this->container(), $this->path(), $paths));
    }

    /**
     * @inheritdoc
     */
    public function container()
    {
        return AssetContainerAPI::find($this->container);
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
            'parent_path' => ($this->parent()) ? $this->parent()->path() : null
        ];
    }

    /**
     * @inheritdoc
     */
    public function editUrl()
    {
        // A folder is currently only editable within a AJAX based modal.
    }
}
