<?php

namespace Statamic\Imaging;

use Facades\Statamic\Imaging\ImageValidator;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;
use League\Glide\Filesystem\FileNotFoundException as GlideFileNotFoundException;
use League\Glide\Manipulators\Watermark;
use League\Glide\Server;
use Statamic\Contracts\Assets\Asset;
use Statamic\Events\GlideImageGenerated;
use Statamic\Facades\Asset as Assets;
use Statamic\Facades\Config;
use Statamic\Facades\File;
use Statamic\Facades\Glide;
use Statamic\Facades\URL;
use Statamic\Support\Str;

class ImageGenerator
{
    /**
     * @var \League\Glide\Server
     */
    private $server;

    /**
     * @var \Statamic\Contracts\Assets\Asset
     */
    private $asset;

    /**
     * @var string
     */
    private $path;

    /**
     * @var array
     */
    private $params;

    /**
     * @var bool
     */
    private $skip_validation;

    /**
     * GlideController constructor.
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    public function getServer(): Server
    {
        return $this->server;
    }

    public function getParams()
    {
        return $this->params;
    }

    public function setParams(array $params)
    {
        if (isset($params['mark'])) {
            $params['mark'] = $this->setUpWatermark($params['mark']);
        }

        $this->params = $params;

        return $this;
    }

    /**
     * Generate a manipulated image by a path.
     *
     * @param  string  $path
     * @return mixed
     */
    public function generateByPath($path, array $params)
    {
        return Glide::cacheStore()->rememberForever(
            'path::'.$path.'::'.md5(json_encode($params)),
            fn () => $this->doGenerateByPath($path, $params)
        );
    }

    private function doGenerateByPath($path, array $params)
    {
        $this->path = $path;
        $this->setParams($params);

        $this->server->setSource($this->pathSourceFilesystem());
        $this->server->setSourcePathPrefix('/');
        $this->server->setCachePathPrefix('paths');

        return $this->generate($path);
    }

    /**
     * Generate a manipulated image by a URL.
     *
     * @param  string  $url
     * @return mixed
     */
    public function generateByUrl($url, array $params)
    {
        return Glide::cacheStore()->rememberForever(
            'url::'.$url.'::'.md5(json_encode($params)),
            fn () => $this->doGenerateByUrl($url, $params)
        );
    }

    private function doGenerateByUrl($url, array $params)
    {
        $this->skip_validation = true;
        $this->setParams($params);

        $parsed = $this->parseUrl($url);
        $qs = $parsed['query'];

        $this->server->setSource($this->guzzleSourceFilesystem($parsed['base']));
        $this->server->setSourcePathPrefix('/');
        $this->server->setCachePathPrefix('http');

        return $this->generate($parsed['path'].($qs ? '?'.$qs : ''));
    }

    /**
     * Generate a manipulated image by an asset.
     *
     * @param  \Statamic\Contracts\Assets\Asset  $asset
     * @return mixed
     */
    public function generateByAsset($asset, array $params)
    {
        $manipulationCacheKey = 'asset::'.$asset->id().'::'.md5(json_encode($params));
        $manifestCacheKey = static::assetCacheManifestKey($asset);

        // Store the cache key for this manipulation in a manifest so that we can easily remove when deleting an asset.
        Glide::cacheStore()->forever(
            $manifestCacheKey,
            collect(Glide::cacheStore()->get($manifestCacheKey, []))->push($manipulationCacheKey)->unique()->all()
        );

        return Glide::cacheStore()->rememberForever(
            $manipulationCacheKey,
            fn () => $this->doGenerateByAsset($asset, $params)
        );
    }

    private function doGenerateByAsset($asset, array $params)
    {
        $this->asset = $asset;
        $this->setParams($params);

        // Set the source of the server to the directory where the requested image will be.
        // Then all we have to do is pass in the basename of the file to be manipulated.
        $this->server->setSource($this->asset->disk()->filesystem()->getDriver());
        $this->server->setSourcePathPrefix($this->asset->folder());

        // Set the cache path so files are saved appropriately.
        $this->server->setCachePathPrefix(self::assetCachePathPrefix($this->asset).'/'.$this->asset->folder());

        return $this->generate($this->asset->basename());
    }

    public static function assetCacheManifestKey($asset)
    {
        return 'asset::'.$asset->id();
    }

    public static function assetCachePathPrefix($asset)
    {
        return 'containers/'.$asset->container()->id();
    }

    /**
     * This one goes to eleven.
     */
    public function toEleven()
    {
        @ini_set('memory_limit', config('statamic.system.php_memory_limit'));

        @set_time_limit(config('statamic.system.php_max_execution_time'));
    }

    private function setUpWatermark($watermark): string
    {
        [$filesystem, $param] = $this->getWatermarkFilesystemAndParam($watermark);

        $this->updateWatermarkFilesystem($filesystem);

        return $param;
    }

    private function getWatermarkFilesystemAndParam($item)
    {
        if (is_string($item) && Str::startsWith($item, 'asset::')) {
            $decoded = Str::fromBase64Url(Str::after($item, 'asset::'));
            [$container, $path] = explode('/', $decoded, 2);
            $item = Assets::find($container.'::'.$path);
        }

        if ($item instanceof Asset) {
            return [$item->disk()->filesystem()->getDriver(), $item->path()];
        }

        if (URL::isAbsolute($item)) {
            $parsed = $this->parseUrl($item);

            return [$this->guzzleSourceFilesystem($parsed['base']), $parsed['path']];
        }

        return [$this->pathSourceFilesystem(), $item];
    }

    private function updateWatermarkFilesystem($filesystem)
    {
        $watermark = new Watermark($filesystem);

        // Since you can't just update the watermark filesystem directly on the server instance, we'll
        // get the api which includes the manipulators, swap the watermark manipulator out for a new
        // one that has the updated filesystem in it. Then push the whole api back onto the server.
        $api = $this->server->getApi();

        $manipulators = collect($api->getManipulators())
            ->map(fn ($manipulator) => $manipulator instanceof Watermark ? $watermark : $manipulator)
            ->all();

        $api->setManipulators($manipulators);

        $this->server->setApi($api);
    }

    /**
     * Generate the image.
     *
     * @param  string  $image  The filename of the image
     * @return mixed
     *
     * @throws \Exception
     * @throws \League\Glide\Filesystem\FileNotFoundException
     * @throws \League\Glide\Filesystem\FilesystemException
     */
    private function generate($image)
    {
        $this->toEleven();

        $this->applyDefaultManipulations();

        if (! $this->skip_validation) {
            $this->validateImage();
        }

        try {
            $path = $this->server->makeImage($image, $this->params);
        } catch (GlideFileNotFoundException $e) {
            throw UnableToReadFile::fromLocation($image);
        }

        GlideImageGenerated::dispatch($path, $this->params);

        return $path;
    }

    /**
     * Apply default Glide manipulations on the image.
     *
     * @return void
     */
    private function applyDefaultManipulations()
    {
        $defaults = Glide::normalizeParameters(
            Config::get('statamic.assets.image_manipulation.defaults') ?: []
        );

        // Enable automatic cropping
        if (Config::get('statamic.assets.auto_crop') && $this->asset) {
            $defaults['fit'] = 'crop-'.$this->asset->get('focus', '50-50');
        }

        $this->server->setDefaults($defaults);
    }

    /**
     * Ensure that the image is actually an image and is allowed to be manipulated.
     *
     * @throws \Exception
     */
    private function validateImage()
    {
        if ($this->asset) {
            $path = $this->asset->path();
            $mime = $this->asset->mimeType();
            $extension = $this->asset->extension();
        } else {
            $path = public_path($this->path);
            if (! File::exists($path)) {
                throw UnableToReadFile::fromLocation($path);
            }
            $mime = File::mimeType($path);
            $extension = File::extension($path);
        }

        if (! ImageValidator::isValidImage($extension, $mime)) {
            throw new \Exception("Image [{$path}] does not actually appear to be a valid image.");
        }
    }

    private function pathSourceFilesystem()
    {
        return Storage::build(['driver' => 'local', 'root' => public_path()])->getDriver();
    }

    private function guzzleSourceFilesystem($base)
    {
        $guzzleClient = app('statamic.imaging.guzzle');

        $adapter = new GuzzleAdapter($base, $guzzleClient);

        return new Filesystem($adapter);
    }

    private function parseUrl($url)
    {
        $parsed = parse_url($url);

        return [
            'path' => Str::after($parsed['path'], '/'),
            'base' => $parsed['scheme'].'://'.$parsed['host'],
            'query' => $parsed['query'] ?? null,
        ];
    }
}
