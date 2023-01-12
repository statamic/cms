<?php

namespace Statamic\Imaging;

use Illuminate\Support\Facades\Storage;
use League\Flysystem\FileNotFoundException as FlysystemFileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\UnableToReadFile;
use League\Glide\Filesystem\FileNotFoundException as GlideFileNotFoundException;
use League\Glide\Manipulators\Watermark;
use League\Glide\Server;
use Statamic\Contracts\Assets\Asset;
use Statamic\Events\GlideImageGenerated;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Asset as Assets;
use Statamic\Facades\Config;
use Statamic\Facades\File;
use Statamic\Facades\Glide;
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
     *
     * @param  \League\Glide\Server  $server
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
     * @param  array  $params
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
     * @param  array  $params
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

        $this->server->setSource($this->guzzleSourceFilesystem($parsed['base']));
        $this->server->setSourcePathPrefix('/');
        $this->server->setCachePathPrefix('http');

        return $this->generate($parsed['path']);
    }

    /**
     * Generate a manipulated image by an asset.
     *
     * @param  \Statamic\Contracts\Assets\Asset  $asset
     * @param  array  $params
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
            $decoded = base64_decode(Str::after($item, 'asset::'));
            [$container, $path] = explode('/', $decoded, 2);
            $item = Assets::find($container.'::'.$path);
        }

        if ($item instanceof Asset) {
            return [$item->disk()->filesystem()->getDriver(), $item->path()];
        }

        if (Str::startsWith($item, ['http://', 'https://'])) {
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
            throw new NotFoundHttpException;
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
     * Ensure that the image is actually an image.
     *
     * @throws \Exception
     */
    private function validateImage()
    {
        if ($this->asset) {
            $path = $this->asset->path();
            $mime = $this->asset->mimeType();
        } else {
            $path = public_path($this->path);
            if (! File::exists($path)) {
                throw $this->isUsingFlysystemOne() ? new FlysystemFileNotFoundException($path) : UnableToReadFile::fromLocation($path);
            }
            $mime = File::mimeType($path);
        }

        if ($mime !== null && strncmp($mime, 'image/', 6) !== 0) {
            throw new \Exception("Image [{$path}] does not actually appear to be an image.");
        }
    }

    private function isUsingFlysystemOne()
    {
        return class_exists('\League\Flysystem\Util');
    }

    private function pathSourceFilesystem()
    {
        return Storage::build(['driver' => 'local', 'root' => public_path()])->getDriver();
    }

    private function guzzleSourceFilesystem($base)
    {
        $guzzleClient = app('statamic.imaging.guzzle');

        $adapter = $this->isUsingFlysystemOne()
            ? new LegacyGuzzleAdapter($base, $guzzleClient)
            : new GuzzleAdapter($base, $guzzleClient);

        return new Filesystem($adapter);
    }

    private function parseUrl($url)
    {
        $parsed = parse_url($url);

        return [
            'path' => Str::after($parsed['path'], '/'),
            'base' => $parsed['scheme'].'://'.$parsed['host'],
        ];
    }
}
