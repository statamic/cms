<?php

namespace Statamic\Imaging;

use GuzzleHttp\Client;
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException as FlysystemFileNotFoundException;
use League\Flysystem\Filesystem;
use League\Glide\Filesystem\FileNotFoundException as GlideFileNotFoundException;
use League\Glide\Server;
use Statamic\Events\GlideImageGenerated;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Config;
use Statamic\Facades\File;

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

    /**
     * Generate a manipulated image by a path.
     *
     * @param  string  $path
     * @param  array  $params
     * @return mixed
     */
    public function generateByPath($path, array $params)
    {
        $this->path = $path;
        $this->params = $params;

        $this->server->setSource(new Filesystem(new Local(public_path())));
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
        $this->skip_validation = true;
        $this->params = $params;

        $parsed = parse_url($url);

        $base = $parsed['scheme'].'://'.$parsed['host'];

        $filesystem = new Filesystem(new GuzzleAdapter($base, new Client()));

        $this->server->setSource($filesystem);
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
        $this->asset = $asset;
        $this->params = $params;

        // Set the source of the server to the directory where the requested image will be.
        // Then all we have to do is pass in the basename of the file to be manipulated.
        $this->server->setSource($this->asset->disk()->filesystem()->getDriver());
        $this->server->setSourcePathPrefix($this->asset->folder());

        // Set the cache path so files are saved appropriately.
        $this->server->setCachePathPrefix('containers/'.$this->asset->container()->id().'/'.$this->asset->folder());

        return $this->generate($this->asset->basename());
    }

    /**
     * This one goes to eleven.
     */
    public function toEleven()
    {
        @ini_set('memory_limit', config('statamic.system.php_memory_limit'));

        @set_time_limit(config('statamic.system.php_max_execution_time'));
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
        $defaults = [];

        // Enable automatic cropping
        if (Config::get('statamic.assets.auto_crop') && $this->asset) {
            $defaults['fit'] = 'crop-'.$this->asset->get('focus', '50-50');
        }

        // TODO: Allow user defined defaults and merge them in here.

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
            throw_unless(File::exists($path), new FlysystemFileNotFoundException($path));
            $mime = File::mimeType($path);
        }

        if ($mime !== null && strncmp($mime, 'image/', 6) !== 0) {
            throw new \Exception("Image [{$path}] does not actually appear to be an image.");
        }
    }
}
