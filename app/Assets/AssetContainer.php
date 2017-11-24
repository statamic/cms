<?php

namespace Statamic\Assets;

use Statamic\API\Folder;
use Statamic\API\Str;
use Statamic\API\Fieldset;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Parse;
use Statamic\API\Asset as AssetAPI;
use Statamic\Events\Data\AssetContainerSaved;
use Statamic\Events\Data\AssetContainerDeleted;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;

class AssetContainer implements AssetContainerContract
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $driver = 'local';

    /**
     * @var string
     */
    protected $path;

    /**
     * @var string
     */
    protected $url;

    /**
     * @var array
     */
    protected $folders = [];

    /**
     * @var string
     */
    protected $fieldset;

    /**
     * Get or set the ID
     *
     * @param null|string $id
     * @return string
     */
    public function id($id = null)
    {
        if (is_null($id)) {
            return $this->id;
        }

        return $this->id = $id;
    }

    /**
     * Get or set the ID, with backwards compatibility
     *
     * @param null|string $uuid
     * @return string
     * @deprecated
     */
    public function uuid($uuid = null)
    {
        return $this->id($uuid);
    }

    /**
     * Get or set the driver
     *
     * @param  null|string $driver
     * @return string
     */
    public function driver($driver = null)
    {
        if (is_null($driver)) {
            return $this->driver;
        }

        return $this->driver = $driver;
    }

    public function data($data = null)
    {
        if (! is_null($data)) {
            $this->data = $data;
            return;
        }

        if ($this->data) {
            return $this->data;
        }

        $path = "assets/{$this->id}.yaml";

        $this->data = YAML::parse(File::disk('content')->get($path));

        return $this->data;
    }

    /**
     * Get or set the title
     *
     * @param null|string $title
     * @return string
     */
    public function title($title = null)
    {
        if ($title) {
            $this->data['title'] = $title;
        }

        return array_get($this->data, 'title', Str::title($this->id));
    }

    /**
     * Get or set the path
     *
     * @param null|string $path
     * @return string
     */
    public function path($path = null)
    {
        if ($path) {
            $this->path = $path;
        }

        return $this->path;
    }

    /**
     * Get the full resolved path
     *
     * @return string
     */
    public function resolvedPath()
    {
        return Parse::env($this->path());
    }

    /**
     * Get or set the URL to this location
     *
     * @param string|null $url
     * @return null|string
     */
    public function url($url = null)
    {
        if (! is_null($url)) {
            $this->url = $url;
        }

        return $this->url;
    }

    /**
     * Convert to an array
     *
     * @return array
     */
    public function toArray()
    {
        $data = $this->data();

        $data['id'] = $this->id();
        $data['driver'] = $this->driver();

        return $data;
    }

    /**
     * Get the URL to edit in the CP
     *
     * @return string
     */
    public function editUrl()
    {
        return cp_route('assets.container.edit', $this->id());
    }

    /**
     * Get or set the fieldset to be used by assets in this container
     *
     * @param string $fieldset
     * @return \Statamic\Contracts\CP\Fieldset
     */
    public function fieldset($fieldset = null)
    {
        if (is_null($fieldset)) {
            if (! $fieldset = array_get($this->data, 'fieldset')) {
                return null;
            }

            return Fieldset::get($fieldset);
        }

        if ($fieldset === false) {
            $fieldset = null;
        }

        $this->data['fieldset'] = $fieldset;
    }

    /**
     * Get or set the handle
     *
     * @param null|string $handle
     * @return string
     */
    public function handle($handle = null)
    {
        // For files, the id is also the handle.
        return $this->id($handle);
    }

    /**
     * Save the container
     *
     * @return void
     */
    public function save()
    {
        $path = "assets/{$this->id}.yaml";

        $data = array_filter($this->toArray());
        unset($data['id']);

        // Get rid of the driver key if it's local. It's local by default.
        if (array_get($data, 'driver') === 'local') {
            unset($data['driver']);
        }

        // Move assets array to the bottom because it's just easier to read.
        if ($assets = array_get($data, 'assets')) {
            unset($data['assets']);
            $data['assets'] = $assets;
        }

        $yaml = YAML::dump($data);

        File::disk('content')->put($path, $yaml);

        event(new AssetContainerSaved($this));
    }

    /**
     * Delete the container
     *
     * @return void
     */
    public function delete()
    {
        $path = "assets/{$this->id}.yaml";

        File::disk('content')->delete($path);

        event(new AssetContainerDeleted($this->id(), $path));
    }

    public function disk($type = 'folder')
    {
        $disk = "assets:{$this->id}";

        return ($type === 'folder') ? Folder::disk($disk) : File::disk($disk);
    }

    /**
     * Get all the asset files in this container
     *
     * @param string|null $folder Narrow down assets by folder
     * @param bool $recursive
     * @return \Illuminate\Support\Collection
     */
    public function files($folder = null, $recursive = false)
    {
        // When requesting files() as-is, we want all of them.
        if ($folder == null) {
            $recursive = true;
        }

        $files = collect($this->disk()->getFiles($folder, $recursive));

        // Get rid of files we never want to show up.
        $files = $files->reject(function ($path) {
            return Str::endsWith($path, ['.DS_Store', 'folder.yaml']);
        });

        return $files->values();
    }

    /**
     * Get all the subfolders in this container
     *
     * @param string|null $folder Narrow down subfolders by folder
     * @param bool $recursive
     * @return \Illuminate\Support\Collection
     */
    public function folders($folder = null, $recursive = false)
    {
        // When requesting folders() as-is, we want all of them.
        if ($folder == null) {
            $folder = '/';
            $recursive = true;
        }

        $folders = collect($this->disk()->getFolders($folder, $recursive));

        return $folders->values();
    }

    /**
     * Get all the assets in this container
     *
     * @param string|null $folder Narrow down assets by folder
     * @param bool $recursive Whether to look for assets recursively
     * @return AssetCollection
     */
    public function assets($folder = null, $recursive = false)
    {
        $assets = $this->files($folder, $recursive)->keyBy(function ($path) {
            return $path;
        })->map(function ($path) {
            return $this->asset($path);
        });

        return collect_assets($assets);
    }

    /**
     * Get all the asset folders in this container
     *
     * @param string|null $folder Narrow down by folder
     */
    public function assetFolders($folder = null)
    {
        return $this->folders($folder)->keyBy(function ($path) {
            return $path;
        })->map(function ($path) {
            return $this->assetFolder($path);
        });
    }

    /**
     * Create an asset
     *
     * @param string $path
     * @return \Statamic\Assets\Asset
     */
    public function createAsset($path)
    {
        return AssetAPI::create($path)->container($this->id)->get();
    }

    /**
     * Find an asset
     *
     * @param string $path
     * @return \Statamic\Assets\Asset|null
     */
    public function asset($path)
    {
        if (! $this->disk()->exists($path)) {
            return;
        }

        $assets = array_get($this->data, 'assets', []);

        $data = array_get($assets, $path, []);

        return AssetAPI::create($path)->container($this->id)->with($data)->get();
    }

    /**
     * Create an asset folder
     *
     * @param string $path
     * @return AssetFolder
     */
    public function assetFolder($path)
    {
        $contents = $this->disk('file')->get("{$path}/folder.yaml", '');

        $data = YAML::parse($contents);

        return new AssetFolder($this->id, $path, $data);
    }

    public function addAsset(\Statamic\Assets\Asset $asset)
    {
        $data = $asset->data();

        $assets = array_get($this->data, 'assets', []);

        $assets[$asset->path()] = $data;

        if (empty($data)) {
            unset($assets[$asset->path()]);
        }

        $this->data['assets'] = $assets;
    }

    public function removeAsset(\Statamic\Assets\Asset $asset)
    {
        $assets = array_get($this->data, 'assets', []);

        unset($assets[$asset->path()]);

        $this->data['assets'] = $assets;
    }

    /**
     * Whether the container's assets are web-accessible
     *
     * @return bool
     */
    public function accessible()
    {
        $driver = $this->driver();

        if ($driver === 's3') {
            return true;
        } elseif ($driver === 'local') {
            return ! is_null($this->url());
        }

        return false;
    }
}
