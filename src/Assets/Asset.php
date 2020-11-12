<?php

namespace Statamic\Assets;

use Facades\Statamic\Assets\Dimensions;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use League\Flysystem\Filesystem;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Data\ContainsData;
use Statamic\Data\Data;
use Statamic\Data\HasAugmentedInstance;
use Statamic\Events\AssetDeleted;
use Statamic\Events\AssetSaved;
use Statamic\Events\AssetUploaded;
use Statamic\Facades;
use Statamic\Facades\AssetContainer as AssetContainerAPI;
use Statamic\Facades\Blueprint;
use Statamic\Facades\File;
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

class Asset implements AssetContract, Augmentable
{
    use HasAugmentedInstance, FluentlyGetsAndSets, ContainsData {
        set as traitSet;
        get as traitGet;
        remove as traitRemove;
        data as traitData;
    }

    protected $container;
    protected $path;
    protected $meta;

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

        return $this->disk()->exists($path);
    }

    public function meta()
    {
        if (! config('statamic.assets.cache_meta')) {
            return $this->generateMeta();
        }

        if ($this->meta) {
            return array_merge($this->meta, ['data' => $this->data->all()]);
        }

        return $this->meta = Cache::rememberForever($this->metaCacheKey(), function () {
            if ($this->disk()->exists($path = $this->metaPath())) {
                return YAML::parse($this->disk()->get($path));
            }

            $this->writeMeta($meta = $this->generateMeta());

            return $meta;
        });
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
        return pathinfo($this->path())['dirname'];
    }

    /**
     * Get or set the path to the data.
     *
     * @param string|null $path Path to set
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
     * @param null|array $params Optional manipulation parameters to return a string right away
     * @return \Statamic\Contracts\Imaging\UrlBuilder|string
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
        return $this->extensionIsOneOf(['h264', 'mp4', 'm4v', 'ogv', 'webm']);
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
     * Get the last modified time of the asset.
     *
     * @return \Carbon\Carbon
     */
    public function lastModified()
    {
        return Carbon::createFromTimestamp($this->meta()['last_modified']);
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
        Cache::forget($this->container()->filesCacheKey());
        Cache::forget($this->container()->filesCacheKey($this->folder()));
    }

    /**
     * Get or set the container where this asset is located.
     *
     * @param string|AssetContainerContract $container  ID of the container
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
     * @param string $filename
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
     * @param string      $folder   The folder relative to the container.
     * @param string|null $filename The new filename, if renaming.
     * @return void
     */
    public function move($folder, $filename = null)
    {
        Cache::forget($this->container()->filesCacheKey($this->folder()));

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
     * @return array  An array in the [width, height] format
     */
    public function dimensions()
    {
        return [$this->meta()['width'], $this->meta()['height']];
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
        if (! $this->isImage()) {
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
        return $this->meta()['size'];
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
     * @param \Symfony\Component\HttpFoundation\File\UploadedFile $file
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

        $this->path($path);

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

        $str = Stringy::create($string)->toAscii();

        foreach ($replacements as $from => $to) {
            $str = $str->replace($from, $to);
        }

        return (string) $str;
    }

    /**
     * Get the blueprint.
     *
     * @param string|null $blueprint
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

    public function __toString()
    {
        return $this->url() ?? $this->id();
    }

    /**
     * Ensure and return unique filename, incrementing as necessary.
     *
     * @param string $folder
     * @param string $filename
     * @param int $count
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

    public function newAugmentedInstance()
    {
        return new AugmentedAsset($this);
    }

    protected function shallowAugmentedArrayKeys()
    {
        return ['id', 'url', 'permalink', 'api_url'];
    }
}
