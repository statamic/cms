<?php

namespace Statamic\Assets;

use Statamic\API;
use Statamic\API\Arr;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Parse;
use Statamic\API\Folder;
use Statamic\API\Stache;
use Statamic\API\Blueprint;
use Statamic\Data\ExistsAsFile;
use Statamic\API\Asset as AssetAPI;
use Statamic\Events\Data\AssetContainerSaved;
use Statamic\Events\Data\AssetContainerDeleted;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;

class AssetContainer implements AssetContainerContract
{
    use ExistsAsFile;

    protected $title;
    protected $handle;
    protected $disk;
    protected $private;
    protected $blueprint;
    protected $assets;

    public function __construct()
    {
        $this->assets = collect();
    }

    public function id($id = null)
    {
        // For files, the handle is the ID.
        return $this->handle(...func_get_args());
    }

    public function handle($handle = null)
    {
        if (func_num_args() === 0) {
            return $this->handle;
        }

        $this->handle = $handle;

        return $this;
    }

    public function title($title = null)
    {
        if (func_num_args() === 0) {
            return $this->title ?? ucfirst($this->handle());
        }

        $this->title = $title;

        return $this;
    }

    public function diskPath()
    {
        return rtrim($this->disk()->path('/'), '/');
    }

    public function path()
    {
        return vsprintf('%s/%s.yaml', [
            rtrim(Stache::store('asset-containers')->directory(), '/'),
            $this->handle()
        ]);
    }

    /**
     * Get the URL to this location
     *
     * @return null|string
     */
    public function url()
    {
        return rtrim($this->disk()->url('/'), '/');
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
        $data['disk'] = array_get($this->data(), 'disk');

        return $data;
    }

    /**
     * Get the URL to edit in the CP
     *
     * @return string
     */
    public function editUrl()
    {
        return cp_route('asset-containers.edit', $this->id());
    }

    public function deleteUrl()
    {
        return cp_route('asset-containers.destroy', $this->id());
    }

    /**
     * Get or set the blueprint to be used by assets in this container
     *
     * @param string $blueprint
     * @return \Statamic\Fields\Blueprint
     */
    public function blueprint($blueprint = null)
    {
        if (func_num_args() === 0) {
            return Blueprint::find($this->blueprint ?? config('statamic.theming.blueprints.asset'))
                ?? Blueprint::find('asset');
        }

        $this->blueprint = $blueprint;

        return $this;
    }

    /**
     * Save the container
     *
     * @return void
     */
    public function save()
    {
        API\AssetContainer::save($this);

        return $this;
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

    public function disk($disk = null)
    {
        if (func_num_args() === 0) {
            return $this->disk ? File::disk($this->disk) : null;
        }

        $this->disk = $disk;

        return $this;
    }

    public function diskHandle()
    {
        return $this->disk;
    }

    public function diskConfig()
    {
        return config("filesystems.disks.{$this->disk}");
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

        if (! $asset = $this->assets->get($path)) {
            $asset = API\Asset::make();
            $asset->container($this);
            $asset->path($path);
        }

        return $asset;
    }

    /**
     * Create an asset folder
     *
     * @param string $path
     * @return AssetFolder
     */
    public function assetFolder($path)
    {
        $data = YAML::parse($this->disk()->get("{$path}/folder.yaml", ''));

        return (new AssetFolder)
            ->container($this)
            ->path($path)
            ->title(array_get($data, 'title'));
    }

    public function addAsset(\Statamic\Assets\Asset $asset)
    {
        $asset->container($this);

        $this->assets[$asset->path()] = $asset;

        return $this;
    }

    public function removeAsset(\Statamic\Assets\Asset $asset)
    {
        $this->assets->forget($asset->path());

        return $this;
    }

    public function pendingAssets()
    {
        return $this->assets;
    }

    /**
     * Whether the container's assets are web-accessible
     *
     * @return bool
     */
    public function accessible()
    {
        return ! $this->private();
    }

    public function private($private = null)
    {
        if (func_num_args() === 0) {
            return (bool) $this->private;
        }

        $this->private = $private;

        return $this;
    }

    protected function fileData()
    {
        return [
            'title' => $this->title(),
            'disk' => $this->disk,
            'blueprint' => $this->blueprint,
            'assets' => $this->assets->map->data()->map(function ($data) {
                return Arr::removeNullValues($data);
            })->reject(function ($data) {
                return empty($data);
            })->all(),
        ];
    }

    public function toCacheableArray()
    {
        return $this->fileData();
    }
}
