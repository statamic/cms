<?php

namespace Statamic\Assets;

use Facades\Statamic\Assets\Dimensions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Contracts\Data\Augmented;
use Statamic\Data\ContainsData;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Data\SyncsOriginalState;
use Statamic\Data\TracksQueriedColumns;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;
use Statamic\Facades;
use Statamic\Facades\AssetContainer as AssetContainerAPI;
use Statamic\Facades\Image;
use Statamic\Facades\Path;
use Statamic\Facades\URL;
use Statamic\Facades\YAML;
use Statamic\Statamic;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Statamic\Support\Traits\FluentlyGetsAndSets;
use Stringy\Stringy;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Mime\MimeTypes;

class Asset implements AssetContract, Augmentable
{
    use HasAugmentedInstance, FluentlyGetsAndSets, TracksQueriedColumns, SyncsOriginalState, ContainsData {
        set as traitSet;
        get as traitGet;
        remove as traitRemove;
        data as traitData;
    }

    protected $container;
    protected $path;
    protected $meta;
    protected $syncOriginalProperties = ['path'];

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

    public function remove($key)
    {
        unset($this->meta['data'][$key]);

        return $this;
    }

    public function data($data = null)
    {
        $this->hydrate();

        return call_user_func_array([$this, 'traitData'], func_get_args());
    }

    public function hydrate()
    {
        $this->meta = $this->meta();

        $this->data = collect($this->meta['data']);

        return $this;
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

    public function meta($key = null)
    {
        if (func_num_args() === 1) {
            return $this->metaValue($key);
        }

        if (! config('statamic.assets.cache_meta')) {
            return $this->generateMeta();
        }

        if ($this->meta) {
            return array_merge($this->meta, ['data' => $this->data->all()]);
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
            $dimensions = Dimensions::asset($this)->get();

            $meta = array_merge($meta, [
                'size' => $this->disk()->size($this->path()),
                'last_modified' => $this->disk()->lastModified($this->path()),
                'width' => $dimensions[0],
                'height' => $dimensions[1],
                'mime_type' => $this->disk()->mimeType($this->path()),
            ]);
        }

        return $meta;
    }

    public function metaPath()
    {
        $path = dirname($this->path()).'/.meta/'.$this->basename().'.yaml';

        return ltrim($path, '/');
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
        return cp_route('assets.thumbnails.show', [
            'encoded_asset' => base64_encode($this->id()),
            'size' => $preset,
        ]);
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
     * Save the asset.
     *
     * @return void
     */
    public function save()
    {
        Facades\Asset::save($this);

        $this->clearCaches();

        AssetSaved::dispatch($this);

        $this->syncOriginal();

        return true;
    }

    /**
     * Delete the asset.
     *
     * @return mixed
     */
    public function delete()
    {
        $this->disk()->delete($this->path());
        $this->disk()->delete($this->metaPath());

        Facades\Asset::delete($this);

        $this->clearCaches();

        AssetDeleted::dispatch($this);

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
     * @return void
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
     * @return void
     */
    public function move($folder, $filename = null)
    {
        $filename = $filename ?: $this->filename();
        $oldPath = $this->path();
        $oldMetaPath = $this->metaPath();
        $newPath = Str::removeLeft(Path::tidy($folder.'/'.$filename.'.'.pathinfo($oldPath, PATHINFO_EXTENSION)), '/');

        if ($oldPath === $newPath) {
            return $this;
        }

        $this->disk()->rename($oldPath, $newPath);
        $this->path($newPath);
        $this->disk()->rename($oldMetaPath, $this->metaPath());
        $this->save();

        return $this;
    }

    /**
     * Get the asset's dimensions.
     *
     * @return array An array in the [width, height] format
     */
    public function dimensions()
    {
        if (! $this->isImage() && ! $this->isSvg()) {
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
     *
     * @return
     */
    public function ratio()
    {
        if (! $this->isImage() && ! $this->isSvg()) {
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
        return $this->basename();
    }

    /**
     * Upload a file.
     *
     * @param  \Symfony\Component\HttpFoundation\File\UploadedFile  $file
     * @return void
     */
    public function upload(UploadedFile $file)
    {
        $ext = $file->getClientOriginalExtension();
        $filename = $this->getSafeFilename(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME));
        $basename = $filename.'.'.$ext;

        $directory = $this->folder();
        $directory = ($directory === '.') ? '/' : $directory;
        $path = Path::tidy($directory.'/'.$filename.'.'.$ext);
        $path = ltrim($path, '/');

        // If the file exists, we'll append a timestamp to prevent overwriting.
        if ($this->disk()->exists($path)) {
            $basename = $filename.'-'.Carbon::now()->timestamp.'.'.$ext;
            $path = Str::removeLeft(Path::assemble($directory, $basename), '/');
        }

        $stream = fopen($file->getRealPath(), 'r');
        $this->disk()->put($path, $stream);
        if (is_resource($stream)) {
            fclose($stream);
        }

        $this->path($path)->syncOriginal();

        $this->save();

        AssetUploaded::dispatch($this);

        return $this;
    }

    private function getSafeFilename($string)
    {
        $replacements = [
            ' ' => '-',
            '#' => '-',
        ];

        $str = Stringy::create(urldecode($string))->toAscii();

        foreach ($replacements as $from => $to) {
            $str = $str->replace($from, $to);
        }

        return (string) $str;
    }

    /**
     * Get the blueprint.
     *
     * @param  string|null  $blueprint
     * @return \Statamic\Fields\Blueprint
     */
    public function blueprint()
    {
        return $this->container()->blueprint();
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
        $suffix = $count ? " ({$count})" : '';
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

    protected function shallowAugmentedArrayKeys()
    {
        return ['id', 'url', 'permalink', 'api_url'];
    }
}
