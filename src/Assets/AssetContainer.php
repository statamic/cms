<?php

namespace Statamic\Assets;

use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Data\ExistsAsFile;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Events\AssetContainerBlueprintFound;
use Statamic\Events\AssetContainerCreated;
use Statamic\Events\AssetContainerDeleted;
use Statamic\Events\AssetContainerSaved;
use Statamic\Events\AssetContainerSaving;
use Statamic\Facades;
use Statamic\Facades\Asset as AssetAPI;
use Statamic\Facades\Blink;
use Statamic\Facades\Blueprint;
use Statamic\Facades\File;
use Statamic\Facades\Image;
use Statamic\Facades\Search;
use Statamic\Facades\Stache;
use Statamic\Facades\URL;
use Statamic\Support\Arr;
use Statamic\Support\Traits\FluentlyGetsAndSets;

class AssetContainer implements AssetContainerContract, Augmentable, ArrayAccess, Arrayable
{
    use ExistsAsFile, FluentlyGetsAndSets, HasAugmentedInstance;

    protected $title;
    protected $handle;
    protected $disk;
    protected $private;
    protected $allowUploads;
    protected $allowDownloading;
    protected $allowMoving;
    protected $allowRenaming;
    protected $createFolders;
    protected $sourcePreset;
    protected $warmPresets;
    protected $searchIndex;
    protected $afterSaveCallbacks = [];
    protected $withEvents = true;
    protected $sortField;
    protected $sortDirection;

    public function id($id = null)
    {
        // For files, the handle is the ID.
        return $this->handle(...func_get_args());
    }

    public function handle($handle = null)
    {
        return $this->fluentlyGetOrSet('handle')->args(func_get_args());
    }

    public function sortField($field = null)
    {
        return $this
            ->fluentlyGetOrSet('sortField')
            ->getter(function ($sortField) {
                if ($sortField) {
                    return $sortField;
                }

                return 'basename';
            })
            ->args(func_get_args());
    }

    public function sortDirection($dir = null)
    {
        return $this
            ->fluentlyGetOrSet('sortDirection')
            ->getter(function ($sortDirection) {
                if ($sortDirection) {
                    return $sortDirection;
                }

                return 'asc';
            })
            ->args(func_get_args());
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
            $this->handle(),
        ]);
    }

    /**
     * Get the URL to this location.
     *
     * @return null|string
     */
    public function url()
    {
        $url = rtrim($this->disk()->url('/'), '/');

        return ($url === '') ? '/' : $url;
    }

    /**
     * Get the absolute URL to this location.
     *
     * @return null|string
     */
    public function absoluteUrl()
    {
        return URL::makeAbsolute($this->url());
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedAssetContainer($this);
    }

    protected function excludedEvaluatedAugmentedArrayKeys()
    {
        return ['assets'];
    }

    /**
     * Get the URL to edit in the CP.
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

    public function apiUrl()
    {
        return null; // TODO
    }

    /**
     * Get the blueprint to be used by assets in this container.
     *
     * @return \Statamic\Fields\Blueprint
     */
    public function blueprint()
    {
        $blueprint = Blueprint::find('assets/'.$this->handle()) ?? Blueprint::makeFromFields([
            'alt' => [
                'type' => 'text',
                'display' => __('Alt Text'),
                'instructions' => __('Description of the image'),
            ],
        ])->setHandle($this->handle())->setNamespace('assets');

        AssetContainerBlueprintFound::dispatch($blueprint, $this);

        return $blueprint;
    }

    public function afterSave($callback)
    {
        $this->afterSaveCallbacks[] = $callback;

        return $this;
    }

    public function saveQuietly()
    {
        $this->withEvents = false;

        return $this->save();
    }

    /**
     * Save the container.
     *
     * @return void
     */
    public function save()
    {
        $isNew = is_null(Facades\AssetContainer::find($this->id()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        $afterSaveCallbacks = $this->afterSaveCallbacks;
        $this->afterSaveCallbacks = [];

        if ($withEvents) {
            if (AssetContainerSaving::dispatch($this) === false) {
                return false;
            }
        }

        Facades\AssetContainer::save($this);

        foreach ($afterSaveCallbacks as $callback) {
            $callback($this);
        }

        if ($withEvents) {
            if ($isNew) {
                AssetContainerCreated::dispatch($this);
            }

            AssetContainerSaved::dispatch($this);
        }

        return $this;
    }

    /**
     * Delete the container.
     *
     * @return void
     */
    public function delete()
    {
        Facades\AssetContainer::delete($this);

        AssetContainerDeleted::dispatch($this);

        return true;
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

    public function listContents()
    {
        return $this->contents()->all();
    }

    public function contents()
    {
        return Blink::once('asset-listing-cache-'.$this->handle(), function () {
            return new AssetContainerContents($this);
        });
    }

    /**
     * Get all the asset files in this container.
     *
     * @param  string|null  $folder  Narrow down assets by folder
     * @param  bool  $recursive
     * @return \Illuminate\Support\Collection
     */
    public function files($folder = '/', $recursive = false)
    {
        // When requesting files() as-is, we want all of them.
        if (func_num_args() === 0) {
            $recursive = true;
        }

        return $this->contents()->filteredFilesIn($folder, $recursive)->keys();
    }

    public function metaFiles($folder = '/', $recursive = false)
    {
        // When requesting files() as-is, we want all of them.
        if (func_num_args() === 0) {
            $recursive = true;
        }

        return $this->contents()->metaFilesIn($folder, $recursive)->keys();
    }

    public function foldersCacheKey($folder = '/', $recursive = false)
    {
        $rec = $recursive ? '-recursive' : '';

        return 'asset-folders-'.$this->handle().'-'.$folder.$rec;
    }

    /**
     * Get all the subfolders in this container.
     *
     * @param  string|null  $folder  Narrow down subfolders by folder
     * @param  bool  $recursive
     * @return \Illuminate\Support\Collection
     */
    public function folders($folder = '/', $recursive = false)
    {
        // When requesting folders() as-is, we want all of them.
        if (func_num_args() === 0) {
            $recursive = true;
        }

        return $this->contents()->filteredDirectoriesIn($folder, $recursive)->keys();
    }

    /**
     * Get all the assets in this container.
     *
     * @param  string|null  $folder  Narrow down assets by folder
     * @param  bool  $recursive  Whether to look for assets recursively
     * @return AssetCollection
     */
    public function assets($folder = '/', $recursive = false)
    {
        $query = $this->queryAssets();

        if (func_num_args() === 0) {
            $recursive = true;
        }

        if ($folder === '/' && $recursive) {
            $folder = null;
        }

        if ($folder !== null) {
            if ($recursive) {
                $query->where('path', 'like', "{$folder}/%");
            } else {
                $query->where('folder', $folder);
            }
        }

        return $query->get();
    }

    /**
     * Get all the asset folders in this container.
     *
     * @param  string  $folder  Narrow down by folder
     * @param  bool  $recursive  Whether to look for subfolders recursively
     * @return Collection A collection of AssetFolder instances
     */
    public function assetFolders($folder = '/', $recursive = false)
    {
        if (func_num_args() === 0) {
            $recursive = true;
        }

        return $this->folders($folder, $recursive)->keyBy(function ($path) {
            return $path;
        })->map(function ($path) {
            return $this->assetFolder($path);
        });
    }

    /**
     * Make an asset.
     *
     * @param  string  $path
     * @return \Statamic\Assets\Asset
     */
    public function makeAsset($path)
    {
        return AssetAPI::make()
            ->path($path)
            ->container($this)
            ->syncOriginal();
    }

    /**
     * Find an asset.
     *
     * @param  string  $path
     * @return \Statamic\Assets\Asset|null
     */
    public function asset($path)
    {
        $asset = $this->makeAsset($path);

        if (! $asset->exists()) {
            return null;
        }

        return $asset->hydrate()->syncOriginal();
    }

    /**
     * Create an asset folder.
     *
     * @param  string  $path
     * @return AssetFolder
     */
    public function assetFolder($path)
    {
        return (new AssetFolder)->container($this)->path($path);
    }

    /**
     * Whether the container's assets are web-accessible.
     *
     * @return bool
     */
    public function accessible()
    {
        $config = $this->disk()->filesystem()->getConfig();

        // If Flysystem 1.x, it will be an array, so wrap it with `collect()` so it can `get()` values;
        // Otherwise it will already be a `ReadOnlyConfiguration` object with a `get()` method.
        if (is_array($config)) {
            $config = collect($config);
        }

        return $config->get('url') !== null;
    }

    /**
     * Whether the container's assets are not web-accessible.
     *
     * @return bool
     */
    public function private()
    {
        return ! $this->accessible();
    }

    /**
     * Enable the quick download button when editing files in this container.
     *
     * @param  bool|null  $allowDownloading
     * @return bool|$this
     */
    public function allowDownloading($allowDownloading = null)
    {
        return $this
            ->fluentlyGetOrSet('allowDownloading')
            ->getter(function ($allowDownloading) {
                return (bool) ($allowDownloading ?? true);
            })
            ->args(func_get_args());
    }

    /**
     * The ability to move files around within this container.
     *
     * @param  bool|null  $allowMoving
     * @return bool|$this
     */
    public function allowMoving($allowMoving = null)
    {
        return $this
            ->fluentlyGetOrSet('allowMoving')
            ->getter(function ($allowMoving) {
                return (bool) ($allowMoving ?? true);
            })
            ->args(func_get_args());
    }

    /**
     * The ability to rename files in this container.
     *
     * @param  bool|null  $allowRenaming
     * @return bool|$this
     */
    public function allowRenaming($allowRenaming = null)
    {
        return $this
            ->fluentlyGetOrSet('allowRenaming')
            ->getter(function ($allowRenaming) {
                return (bool) ($allowRenaming ?? true);
            })
            ->args(func_get_args());
    }

    /**
     * The ability to upload into this container.
     *
     * @param  bool|null  $allowUploads
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
     * @param  bool|null  $createFolders
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

    /**
     * The glide source preset to be permanently applied to source image on upload.
     *
     * @param  string|null  $preset
     * @return string|null|$this
     */
    public function sourcePreset($preset = null)
    {
        return $this
            ->fluentlyGetOrSet('sourcePreset')
            ->args(func_get_args());
    }

    /**
     * The specific glide presets to be used when warming glide image cache on upload.
     *
     * @param  array|null  $presets
     * @return array|null|$this
     */
    public function warmPresets($preset = null)
    {
        return $this
            ->fluentlyGetOrSet('warmPresets')
            ->getter(function ($presets) {
                if ($presets === false) {
                    return [];
                }

                if ($presets !== null) {
                    return $presets;
                }

                $presets = Image::userManipulationPresets();

                $presets = Arr::except($presets, $this->sourcePreset);

                return array_keys($presets);
            })
            ->setter(function ($presets) {
                return $presets === [] ? false : $presets;
            })
            ->args(func_get_args());
    }

    public function warmsPresetsIntelligently()
    {
        return $this->warmPresets === null;
    }

    public function fileData()
    {
        $array = [
            'title' => $this->title,
            'disk' => $this->disk,
            'search_index' => $this->searchIndex,
            'allow_uploads' => $this->allowUploads,
            'allow_downloading' => $this->allowDownloading,
            'allow_renaming' => $this->allowRenaming,
            'allow_moving' => $this->allowMoving,
            'create_folders' => $this->createFolders,
            'source_preset' => $this->sourcePreset,
            'warm_presets' => $this->warmPresets,
        ];

        $array = Arr::removeNullValues(array_merge($array, [
            'sort_by' => $this->sortField,
            'sort_dir' => $this->sortDirection,
        ]));

        return $array;
    }

    public function queryAssets()
    {
        return Facades\Asset::query()->where('container', $this);
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

    public static function __callStatic($method, $parameters)
    {
        return Facades\AssetContainer::{$method}(...$parameters);
    }
}
