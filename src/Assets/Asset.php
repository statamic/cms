<?php

namespace Statamic\Assets;

use ArrayAccess;
use Facades\Statamic\Assets\Attributes;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Statamic\Assets\AssetUploader as Uploader;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Contracts\GraphQL\ResolvesValues as ResolvesValuesContract;
use Statamic\Contracts\Query\ContainsQueryableValues;
use Statamic\Contracts\Search\Searchable as SearchableContract;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\HasDirtyState;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Data\TracksQueriedRelations;
use Statamic\Events\AssetContainerBlueprintFound;
use Statamic\Events\AssetCreated;
use Statamic\Events\AssetCreating;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetDeleting;
use Statamic\Events\AssetReplaced;
use Statamic\Events\AssetReuploaded;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetSaving;
use Statamic\Events\AssetUploaded;
use Statamic\Exceptions\FileExtensionMismatch;
use Statamic\Facades;
use Statamic\Facades\AssetContainer as AssetContainerAPI;
use Statamic\Facades\Blink;
use Statamic\Facades\Image;
use Statamic\Facades\Path;
use Statamic\Facades\URL;
use Statamic\Facades\YAML;
use Statamic\GraphQL\ResolvesValues;
use Statamic\Listeners\UpdateAssetReferences as UpdateAssetReferencesSubscriber;
use Statamic\Search\Searchable;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

class Asset implements Arrayable, ArrayAccess, AssetContract, Augmentable, ContainsQueryableValues, ResolvesValuesContract, SearchableContract
{
    use ContainsData, FluentlyGetsAndSets, HasAugmentedInstance, HasDirtyState,
        Searchable,
        TracksQueriedColumns, TracksQueriedRelations {
            set as traitSet;
            get as traitGet;
            remove as traitRemove;
            data as traitData;
            merge as traitMerge;
        }
    use ResolvesValues {
        resolveGqlValue as traitResolveGqlValue;
    }

    protected $container;
    protected $path;
    protected $meta;
    protected $withEvents = true;
    protected $shouldHydrate = true;
    protected $removedData = [];

    public function syncOriginal()
    {
        $this->original = [];

        foreach (['path'] as $property) {
            $this->original[$property] = $this->{$property};
        }

        $this->original['data'] = new PendingMeta('data');

        return $this;
    }

    public function getOriginal($key = null, $fallback = null)
    {
        $this->resolvePendingMetaOriginalValues();

        return Arr::get($this->original, $key, $fallback);
    }

    private function resolvePendingMetaOriginalValues()
    {
        if (empty($this->original)) {
            $this->syncOriginal();
        }

        // If it's an array, they've already been resolved.
        if (is_array($this->original['data'])) {
            return;
        }

        $this->original['data'] = $this->metaExists() ? $this->meta('data') : $this->data->all();
    }

    public function getRawOriginal()
    {
        return $this->original;
    }

    public function __construct()
    {
        $this->data = collect();
        $this->supplements = collect();
    }

    public function id($id = null)
    {
        if ($id) {
            throw new \Exception('Asset IDs cannot be set directly.');
        }

        return $this->container->id().'::'.$this->path();
    }

    public function reference()
    {
        return "asset::{$this->id()}";
    }

    public function get($key, $fallback = null)
    {
        return $this->hydrate()->traitGet($key, $fallback);
    }

    public function set($key, $value)
    {
        return $this->hydrate()->traitSet($key, $value);
    }

    public function merge($value)
    {
        return $this->hydrate()->traitMerge($value);
    }

    public function remove($key)
    {
        $this->hydrate();

        $this->removedData[] = $key;

        return $this->traitRemove($key);
    }

    public function data($data = null)
    {
        $this->hydrate();

        if (func_get_args()) {
            $this->removedData = collect($this->meta['data'])
                ->diffKeys($data)
                ->keys()
                ->merge($this->removedKeys)
                ->all();
        }

        return call_user_func_array([$this, 'traitData'], func_get_args());
    }

    public function hydrate()
    {
        if (! $this->shouldHydrate) {
            return $this;
        }

        $this->meta = $this->meta();

        $this->data = collect($this->meta['data']);

        $this->removedData = [];

        if (! empty($this->original)) {
            $this->resolvePendingMetaOriginalValues();
        }

        return $this;
    }

    public function withoutHydrating($callback)
    {
        $this->shouldHydrate = false;

        $return = $callback($this);

        $this->shouldHydrate = true;

        return $return;
    }

    /**
     * Get the container's filesystem disk instance.
     *
     * @return \Statamic\Filesystem\FlysystemAdapter
     */
    public function disk()
    {
        return $this->container()->disk();
    }

    public function exists()
    {
        if (! $path = $this->path()) {
            return false;
        }

        return $this->container()->files()->contains($path);
    }

    public function getRawMeta()
    {
        return $this->meta;
    }

    public function meta($key = null)
    {
        if (func_num_args() === 1) {
            return $this->metaValue($key);
        }

        if (! $this->exists()) {
            return $this->generateMeta();
        }

        if (! config('statamic.assets.cache_meta')) {
            return $this->generateMeta();
        }

        if ($this->meta) {
            $meta = $this->meta;

            $meta['data'] = collect(Arr::get($meta, 'data', []))
                ->merge($this->data->all())
                ->except($this->removedData)
                ->all();

            return $meta;
        }

        return $this->meta = Cache::rememberForever($this->metaCacheKey(), function () {
            if ($contents = $this->disk()->get($path = $this->metaPath())) {
                return YAML::file($path)->parse($contents);
            }

            $this->writeMeta($meta = $this->generateMeta());

            return $meta;
        });
    }

    private function metaValue($key)
    {
        $value = Arr::get($this->meta(), $key);

        if (! is_null($value)) {
            return $value;
        }

        Cache::forget($this->metaCacheKey());

        $this->writeMeta($meta = $this->generateMeta());

        return Arr::get($meta, $key);
    }

    public function generateMeta()
    {
        $meta = ['data' => $this->data->all()];

        if ($this->exists()) {
            $attributes = Attributes::asset($this)->get();

            $meta = array_merge($meta, [
                'size' => $this->disk()->size($this->path()),
                'last_modified' => $this->disk()->lastModified($this->path()),
                'width' => Arr::get($attributes, 'width'),
                'height' => Arr::get($attributes, 'height'),
                'mime_type' => $this->disk()->mimeType($this->path()),
                'duration' => Arr::get($attributes, 'duration'),
            ]);
        }

        return $meta;
    }

    public function metaPath()
    {
        $path = dirname($this->path()).'/.meta/'.$this->basename().'.yaml';

        return (string) Str::of($path)->replaceFirst('./', '')->ltrim('/');
    }

    protected function metaExists()
    {
        return $this->container()->metaFiles()->contains($this->metaPath());
    }

    public function writeMeta($meta)
    {
        $meta['data'] = Arr::removeNullValues($meta['data']);

        $contents = YAML::dump($meta);

        $this->disk()->put($this->metaPath(), $contents);
    }

    public function metaCacheKey()
    {
        return 'asset-meta-'.$this->id();
    }

    /**
     * Get the filename of the asset.
     *
     * Eg. For a path of foo/bar/baz.jpg, the filename will be "baz"
     *
     * @return string
     */
    public function filename()
    {
        return pathinfo($this->path())['filename'];
    }

    /**
     * Get the basename of the asset.
     *
     * Eg. for a path of foo/bar/baz.jpg, the basename will be "baz.jpg"
     *
     * @return string
     */
    public function basename()
    {
        return pathinfo($this->path())['basename'];
    }

    /**
     * Get the folder (or directory) of the asset.
     *
     * Eg. for a path of foo/bar/baz.jpg, the folder will be "foo/bar"
     *
     * @return mixed
     */
    public function folder()
    {
        $dirname = pathinfo($this->path())['dirname'];

        return $dirname === '.' ? '/' : $dirname;
    }

    /**
     * Get or set the path to the data.
     *
     * @param  string|null  $path  Path to set
     * @return mixed
     */
    public function path($path = null)
    {
        return $this
            ->fluentlyGetOrSet('path')
            ->getter(function ($path) {
                return $path ? ltrim($path, '/') : null;
            })
            ->args(func_get_args());
    }

    /**
     * Get the resolved path to the asset.
     *
     * This is the "actual" path to the asset.
     * It combines the container path with the asset path.
     *
     * @return string
     */
    public function resolvedPath()
    {
        return Path::tidy($this->container()->diskPath().'/'.$this->path());
    }

    /**
     * Get the asset's URL.
     *
     * @return string
     */
    public function url()
    {
        if ($this->container()->private()) {
            return null;
        }

        return URL::assemble($this->container()->url(), $this->path());
    }

    public function absoluteUrl()
    {
        if ($this->container()->private()) {
            return null;
        }

        return URL::assemble($this->container()->absoluteUrl(), $this->path());
    }

    public function thumbnailUrl($preset = null)
    {
        if ($this->isSvg()) {
            return $this->svgThumbnailUrl();
        }

        return cp_route('assets.thumbnails.show', [
            'encoded_asset' => base64_encode($this->id()),
            'size' => $preset,
        ]);
    }

    protected function svgThumbnailUrl()
    {
        if ($url = $this->url()) {
            return $url;
        }

        return cp_route('assets.svgs.show', ['encoded_asset' => base64_encode($this->id())]);
    }

    public function pdfUrl()
    {
        return cp_route('assets.pdfs.show', ['encoded_asset' => base64_encode($this->id())]);
    }

    /**
     * Get either a image URL builder instance, or a URL if passed params.
     *
     * @param  null|array  $params  Optional manipulation parameters to return a string right away
     * @return \Statamic\Contracts\Imaging\UrlBuilder|string
     *
     * @throws \Exception
     */
    public function manipulate($params = null)
    {
        return Image::manipulate($this, $params);
    }

    /**
     * Is this asset an audio file?
     *
     * @return bool
     */
    public function isAudio()
    {
        return $this->extensionIsOneOf(['aac', 'flac', 'm4a', 'mp3', 'ogg', 'wav']);
    }

    /**
     * Is this asset a Google Docs previewable file?
     * https://gist.github.com/izazueta/4961650.
     *
     * @return bool
     */
    public function isPreviewable()
    {
        return $this->extensionIsOneOf([
            'doc', 'docx', 'pages', 'txt',
            'ai', 'psd', 'eps', 'ps',
            'css', 'html', 'php', 'c', 'cpp', 'h', 'hpp', 'js',
            'ppt', 'pptx',
            'flv',
            'tiff',
            'ttf',
            'dxf', 'xps',
            'zip', 'rar',
            'xls', 'xlsx',
            'pdf',
        ]);
    }

    /**
     * Is this asset an image?
     *
     * @return bool
     */
    public function isImage()
    {
        return $this->extensionIsOneOf(['jpg', 'jpeg', 'png', 'gif', 'webp']);
    }

    /**
     * Is this asset an svg?
     *
     * @return bool
     */
    public function isSvg()
    {
        return $this->extensionIsOneOf(['svg']);
    }

    /**
     * Is this asset a video file?
     *
     * @return bool
     */
    public function isVideo()
    {
        return $this->extensionIsOneOf(['h264', 'mp4', 'm4v', 'ogv', 'webm', 'mov']);
    }

    /**
     * Is this asset a media file?
     *
     * @return bool
     */
    public function isMedia()
    {
        return $this->isImage()
                || $this->isSvg()
                || $this->isVideo()
                || $this->isAudio();
    }

    /**
     * Is this asset a PDF?
     *
     * @return bool
     */
    public function isPdf()
    {
        return $this->extensionIsOneOf(['pdf']);
    }

    /**
     * Get the file download url.
     *
     * @return string
     */
    public function cpDownloadUrl()
    {
        return cp_route('assets.download', base64_encode($this->id()));
    }

    /**
     * Get the file extension of the asset.
     *
     * @return string
     */
    public function extension()
    {
        return Path::extension($this->path());
    }

    /**
     * Get the extension based on the mime type.
     *
     * @return string|null The guessed extension or null if it cannot be guessed
     */
    public function guessedExtension()
    {
        return MimeTypes::getDefault()->getExtensions($this->mimeType())[0] ?? null;
    }

    /**
     * Get the mime type.
     *
     * @return string
     */
    public function mimeType()
    {
        return $this->meta('mime_type');
    }

    /**
     * Get the last modified time of the asset.
     *
     * @return \Carbon\Carbon
     */
    public function lastModified()
    {
        return Carbon::createFromTimestamp($this->meta('last_modified'));
    }

    /**
     * Save quietly without firing events.
     *
     * @return bool
     */
    public function saveQuietly()
    {
        $this->withEvents = false;

        return $this->save();
    }

    /**
     * Save the asset.
     *
     * @return bool
     */
    public function save()
    {
        $isNew = is_null($this->container()->asset($this->path()));

        $withEvents = $this->withEvents;
        $this->withEvents = true;

        if ($withEvents) {
            if ($isNew && AssetCreating::dispatch($this) === false) {
                return false;
            }

            if (AssetSaving::dispatch($this) === false) {
                return false;
            }
        }

        Facades\Asset::save($this);

        $this->clearCaches();

        if ($withEvents) {
            if ($isNew) {
                AssetCreated::dispatch($this);
            }

            AssetSaved::dispatch($this);
        }

        $this->syncOriginal();

        return true;
    }

    /**
     * Delete quietly without firing events.
     *
     * @return bool
     */
    public function deleteQuietly()
    {
        $this->withEvents = false;

        return $this->delete();
    }

    /**
     * Delete the asset.
     *
     * @return $this
     */
    public function delete()
    {
        $withEvents = $this->withEvents;
        $this->withEvents = true;

        if ($withEvents && AssetDeleting::dispatch($this) === false) {
            return false;
        }

        $this->disk()->delete($this->path());
        $this->disk()->delete($this->metaPath());

        Facades\Asset::delete($this);

        $this->clearCaches();

        if ($withEvents) {
            AssetDeleted::dispatch($this);
        }

        return $this;
    }

    /**
     * Clear meta and filesystem listing caches.
     */
    private function clearCaches()
    {
        $this->meta = null;
        Cache::forget($this->metaCacheKey());
    }

    /**
     * Get or set the container where this asset is located.
     *
     * @param  string|AssetContainerContract  $container  ID of the container
     * @return AssetContainerContract
     */
    public function container($container = null)
    {
        return $this
            ->fluentlyGetOrSet('container')
            ->setter(function ($container) {
                return is_string($container) ? AssetContainerAPI::find($container) : $container;
            })
            ->args(func_get_args());
    }

    /**
     * Get the container's ID.
     *
     * @return string
     */
    public function containerId()
    {
        return $this->container->id();
    }

    /**
     * Get the container's handle.
     *
     * @return string
     */
    public function containerHandle()
    {
        return $this->container->handle();
    }

    /**
     * Rename the asset.
     *
     * @param  string  $filename
     * @return self
     */
    public function rename($filename, $unique = false)
    {
        $filename = $unique ? $this->ensureUniqueFilename($this->folder(), $filename) : $filename;

        return $this->move($this->folder(), $filename);
    }

    /**
     * Move the asset to a different location.
     *
     * @param  string  $folder  The folder relative to the container.
     * @param  string|null  $filename  The new filename, if renaming.
     * @return $this
     */
    public function move($folder, $filename = null)
    {
        $filename = Uploader::getSafeFilename($filename ?: $this->filename());
        $oldPath = $this->path();
        $oldMetaPath = $this->metaPath();
        $newPath = Str::removeLeft(Path::tidy($folder.'/'.$filename.'.'.pathinfo($oldPath, PATHINFO_EXTENSION)), '/');

        if ($oldPath === $newPath) {
            return $this;
        }

        $this->hydrate();
        $this->disk()->rename($oldPath, $newPath);
        $this->path($newPath);
        $this->save();

        $this->disk()->rename($oldMetaPath, $this->metaPath());

        return $this;
    }

    /**
     * Replace an asset and/or its references where necessary.
     *
     * @param  bool  $deleteOriginal
     * @return $this
     */
    public function replace(Asset $originalAsset, $deleteOriginal = false)
    {
        // Temporarily disable the reference updater to avoid triggering reference updates
        // until after the `AssetReplaced` event is fired. We still want to fire events
        // like `AssetDeleted` and `AssetSaved` though, so that other listeners will
        // get triggered (for cache invalidation, clearing of glide cache, etc.)
        UpdateAssetReferencesSubscriber::disable();

        if ($deleteOriginal) {
            $originalAsset->delete();
        }

        UpdateAssetReferencesSubscriber::enable();

        AssetReplaced::dispatch($originalAsset, $this);

        return $this;
    }

    /**
     * Get the asset's dimensions.
     *
     * @return array An array in the [width, height] format
     */
    public function dimensions()
    {
        if (! $this->hasDimensions()) {
            return [null, null];
        }

        return [$this->meta('width'), $this->meta('height')];
    }

    /**
     * Get the asset's width.
     *
     * @return int|null
     */
    public function width()
    {
        return $this->dimensions()[0];
    }

    /**
     * Get the asset's height.
     *
     * @return int|null
     */
    public function height()
    {
        return $this->dimensions()[1];
    }

    /**
     * Get the asset's duration.
     *
     * @return float|null
     */
    public function duration()
    {
        if (! $this->hasDuration()) {
            return null;
        }

        return $this->meta('duration');
    }

    /**
     * Get the asset's orientation.
     *
     * @return string|null
     */
    public function orientation()
    {
        if ($this->height() > $this->width()) {
            return 'portrait';
        } elseif ($this->height() < $this->width()) {
            return 'landscape';
        } elseif ($this->height() === $this->width()) {
            return 'square';
        }

        return null;
    }

    /**
     * Get the asset's ratio.
     */
    public function ratio()
    {
        if (! $this->hasDimensions()) {
            return null;
        }

        if ($this->height() == 0) {
            return null;
        }

        return $this->width() / $this->height();
    }

    /**
     * Get the asset's file size.
     *
     * @return int
     */
    public function size()
    {
        return $this->meta('size');
    }

    /**
     * Get the display name of the asset.
     *
     * Typically used when an asset could be amongst other
     * types of objects, like within search results.
     *
     * @return string
     */
    public function title()
    {
        return $this->get('title') ?? $this->basename();
    }

    /**
     * Upload a file.
     *
     * @return $this
     */
    public function upload(UploadedFile $file)
    {
        if (AssetCreating::dispatch($this) === false) {
            return false;
        }

        $path = Uploader::asset($this)->upload($file);

        $this
            ->path($path)
            ->syncOriginal()
            ->save();

        AssetUploaded::dispatch($this);

        AssetCreated::dispatch($this);

        return $this;
    }

    public function reupload(ReplacementFile $file)
    {
        if ($file->extension() !== $this->extension()) {
            throw new FileExtensionMismatch('The file extension must match the original file.');
        }

        $file->writeTo($this->disk()->filesystem(), $this->path());

        $this->clearCaches();
        $this->writeMeta($this->generateMeta());

        AssetReuploaded::dispatch($this);

        return $this;
    }

    /**
     * Download a file.
     *
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function download(?string $name = null, array $headers = [])
    {
        return $this->disk()->filesystem()->download($this->path(), $name, $headers);
    }

    /**
     * Stream a file.
     *
     * @return resource
     */
    public function stream()
    {
        return $this->disk()->filesystem()->readStream($this->path());
    }

    /**
     * Get the asset file contents.
     *
     * @return mixed
     */
    public function contents()
    {
        return $this->disk()->get($this->path());
    }

    /**
     * Get the blueprint.
     *
     * @param  string|null  $blueprint
     * @return \Statamic\Fields\Blueprint
     */
    public function blueprint()
    {
        $key = "asset-{$this->id()}-blueprint";

        if (Blink::has($key)) {
            return Blink::get($key);
        }

        $blueprint = $this->container()->blueprint($this);

        Blink::put($key, $blueprint);

        AssetContainerBlueprintFound::dispatch($blueprint, $this->container(), $this);

        return $blueprint;
    }

    /**
     * The URL to edit it in the CP.
     *
     * @return mixed
     */
    public function editUrl()
    {
        return cp_route('assets.browse.edit', $this->container()->handle().'/'.$this->path());
    }

    public function apiUrl()
    {
        return Statamic::apiRoute('assets.show', [$this->containerHandle(), $this->path()]);
    }

    /**
     * Check if asset's file extension is one of a given list.
     *
     * @return bool
     */
    public function extensionIsOneOf($filetypes = [])
    {
        return in_array(strtolower($this->extension()), $filetypes);
    }

    /**
     * Check if asset's guessed file extension is one of a given list.
     *
     * @return string
     */
    public function guessedExtensionIsOneOf($filetypes = [])
    {
        return in_array(strtolower($this->guessedExtension()), $filetypes);
    }

    public function __toString()
    {
        return $this->url() ?? $this->id();
    }

    /**
     * Ensure and return unique filename, incrementing as necessary.
     *
     * @param  string  $folder
     * @param  string  $filename
     * @param  int  $count
     * @return string
     */
    protected function ensureUniqueFilename($folder, $filename, $count = 0)
    {
        $extension = pathinfo($this->path(), PATHINFO_EXTENSION);
        $suffix = $count ? "-{$count}" : '';
        $newPath = Str::removeLeft(Path::tidy($folder.'/'.$filename.$suffix.'.'.$extension), '/');

        if ($this->disk()->exists($newPath)) {
            return $this->ensureUniqueFilename($folder, $filename, $count + 1);
        }

        return $filename.$suffix;
    }

    public static function __callStatic($method, $parameters)
    {
        return Facades\Asset::{$method}(...$parameters);
    }

    public function newAugmentedInstance(): Augmented
    {
        return new AugmentedAsset($this);
    }

    public function defaultAugmentedArrayKeys()
    {
        return $this->selectedQueryColumns;
    }

    public function shallowAugmentedArrayKeys()
    {
        return ['id', 'url', 'permalink', 'api_url', 'alt'];
    }

    protected function defaultAugmentedRelations()
    {
        return $this->selectedQueryRelations;
    }

    private function hasDimensions()
    {
        return $this->isImage() || $this->isSvg() || $this->isVideo();
    }

    private function hasDuration()
    {
        return $this->isAudio() || $this->isVideo();
    }

    public function getQueryableValue(string $field)
    {
        if (method_exists($this, $method = Str::camel($field))) {
            return $this->{$method}();
        }

        $value = $this->get($field);

        if (! $field = $this->blueprint()->field($field)) {
            return $value;
        }

        return $field->fieldtype()->toQueryableValue($value);
    }

    public function getCurrentDirtyStateAttributes(): array
    {
        return array_merge([
            'path' => $this->path(),
            'data' => $this->data()->toArray(),
        ]);
    }

    public function getCpSearchResultBadge(): string
    {
        return $this->container()->title();
    }

    public function warmPresets()
    {
        if (! $this->isImage()) {
            return [];
        }

        $cpPresets = config('statamic.cp.enabled') ? [
            'cp_thumbnail_small_'.$this->orientation(),
        ] : [];

        return array_merge($this->container->warmPresets(), $cpPresets);
    }
}
