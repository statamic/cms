<?php

namespace Statamic\Assets;

use Statamic\API;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\API\Parse;
use Statamic\API\Folder;
use Statamic\API\Search;
use Statamic\API\Stache;
use Statamic\API\Blueprint;
use Statamic\Data\ExistsAsFile;
use Statamic\FluentlyGetsAndSets;
use Statamic\API\Asset as AssetAPI;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Events\Data\AssetContainerSaved;
use Statamic\Events\Data\AssetContainerDeleted;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;

class AssetContainer implements AssetContainerContract, Augmentable
{
    use ExistsAsFile, FluentlyGetsAndSets;

    protected $title;
    protected $handle;
    protected $disk;
    protected $blueprint;
    protected $private;
    protected $allowUploads;
    protected $createFolders;
    protected $searchIndex;

    public function id($id = null)
    {
        // For files, the handle is the ID.
        return $this->handle(...func_get_args());
    }

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function title($title = null)
    {
        return $this
            ->fluentlyGetOrSet('title')
            ->getter(function ($title) {
                return $title ?? ucfirst($this->handle);
            })
            ->args(func_get_args());
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
        return [
            'title' => $this->title(),
            'disk' => $this->disk,
            'blueprint' => $this->blueprint,
            'allow_uploads' => $this->allowUploads(),
            'create_folders' => $this->createFolders(),
            'search_index' => $this->searchIndex,
        ];
    }

    public function toAugmentedArray()
    {
        return array_merge($this->toArray(), [
            'handle' => $this->handle(),
            'assets' => $this->assets()
        ]);
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

    public function showUrl()
    {
        return cp_route('assets.browse.show', $this->handle());
    }

    /**
     * Get or set the blueprint to be used by assets in this container
     *
     * @param string $blueprint
     * @return \Statamic\Fields\Blueprint|$this
     */
    public function blueprint($blueprint = null)
    {
        return $this
            ->fluentlyGetOrSet('blueprint')
            ->getter(function ($blueprint) {
                return Blueprint::find($blueprint ?? config('statamic.theming.blueprints.asset'))
                    ?? Blueprint::find('asset');
            })
            ->args(func_get_args());
    }

    /**
     * Save the container
     *
     * @return void
     */
    public function save()
    {
        API\AssetContainer::save($this);

        // event(new AssetContainerSaved($this));

        return $this;
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
        return $this
            ->fluentlyGetOrSet('disk')
            ->getter(function ($disk) {
                return $disk ? File::disk($disk) : null;
            })
            ->args(func_get_args());
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
            return Str::startsWith($path, '.meta/')
                || Str::contains($path, '/.meta/')
                || Str::endsWith($path, ['.DS_Store']);
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

        $paths = $this->disk()->getFolders($folder, $recursive);

        return collect($paths)->reject(function ($path) {
            return basename($path) === '.meta';
        })->values();
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
        $query = $this->queryAssets();

        if ($folder && $recursive) {
            $query->where('folder', 'like', "{$folder}%");
        } elseif ($folder) {
            $query->where('folder', $folder);
        }

        return $query->get();
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
     * Make an asset
     *
     * @param string $path
     * @return \Statamic\Assets\Asset
     */
    public function makeAsset($path)
    {
        return AssetAPI::make()->path($path)->container($this);
    }

    /**
     * Find an asset
     *
     * @param string $path
     * @return \Statamic\Assets\Asset|null
     */
    public function asset($path)
    {
        $asset = API\Asset::make()->container($this)->path($path);

        if (! $asset->disk()->exists($asset->path())) {
            return null;
        }

        $asset->hydrate();

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
        return $this
            ->fluentlyGetOrSet('private')
            ->getter(function ($private) {
                return (bool) $private;
            })
            ->args(func_get_args());
    }

    /**
     * The ability to upload into this container.
     *
     * @param bool|null $allowUploads
     * @return bool|$this
     */
    public function allowUploads($allowUploads = null)
    {
        return $this
            ->fluentlyGetOrSet('allowUploads')
            ->getter(function ($allowUploads) {
                return (bool) ($allowUploads ?? true);
            })
            ->args(func_get_args());
    }

    /**
     * The ability to create folders within this container.
     *
     * @param bool|null $createFolders
     * @return bool|$this
     */
    public function createFolders($createFolders = null)
    {
        return $this
            ->fluentlyGetOrSet('createFolders')
            ->getter(function ($createFolders) {
                return (bool) ($createFolders ?? true);
            })
            ->args(func_get_args());
    }

    protected function fileData()
    {
        $data = $this->toArray();

        if ($data['allow_uploads'] === true) {
            unset($data['allow_uploads']);
        }

        if ($data['create_folders'] === true) {
            unset($data['create_folders']);
        }

        return $data;
    }

    public function toCacheableArray()
    {
        return $this->fileData();
    }

    public function queryAssets()
    {
        return API\Asset::query()->where('container', $this);
    }

    public function hasSearchIndex()
    {
        return $this->searchIndex() !== null;
    }

    public function searchIndex($index = null)
    {
        return $this
            ->fluentlyGetOrSet('searchIndex')
            ->getter(function ($index) {
                return $index ? Search::index($index) : null;
            })
            ->args(func_get_args());
    }
}
