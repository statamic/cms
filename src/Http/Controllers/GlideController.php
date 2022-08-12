<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\UnableToReadFile;
use League\Glide\Server;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Config;
use Statamic\Facades\Site;
use Statamic\Imaging\ImageGenerator;
use Statamic\Support\Str;

class GlideController extends Controller
{
    /**
     * @var \League\Glide\Server
     */
    private $server;

    /**
     * @var \Illuminate\Http\Request
     */
    private $request;

    /**
     * @var ImageGenerator
     */
    private $generator;

    /**
     * GlideController constructor.
     *
     * @param  \League\Glide\Server  $server
     * @param  \Illuminate\Http\Request  $request
     */
    public function __construct(Server $server, Request $request)
    {
        $this->server = $server;
        $this->request = $request;
        $this->generator = new ImageGenerator($server);
    }

    /**
     * Generate a manipulated image by a path.
     *
     * @param  string  $path
     * @return mixed
     */
    public function generateByPath($path)
    {
        $this->validateSignature();

        // If the auto crop setting is enabled, we will attempt to resolve an asset from the
        // given path in order to get its focal point. A little overhead for convenience.
        if (Config::get('statamic.assets.auto_crop')) {
            if ($asset = Asset::find(Str::ensureLeft($path, '/'))) {
                return $this->createResponse($this->generateBy('asset', $asset));
            }
        }

        return $this->createResponse($this->generateBy('path', $path));
    }

    /**
     * Generate a manipulated image by a URL.
     *
     * @param  string  $url
     * @return mixed
     */
    public function generateByUrl($url)
    {
        $this->validateSignature();

        $url = base64_decode($url);

        return $this->createResponse($this->generateBy('url', $url));
    }

    /**
     * Generate a manipulated image by an asset reference.
     *
     * @param  string  $ref
     * @return mixed
     *
     * @throws \Exception
     */
    public function generateByAsset($encoded)
    {
        $this->validateSignature();

        $decoded = base64_decode($encoded);

        // The string before the first slash is the container
        [$container, $path] = explode('/', $decoded, 2);

        throw_unless($container = AssetContainer::find($container), new NotFoundHttpException);

        throw_unless($asset = $container->asset($path), new NotFoundHttpException);

        return $this->createResponse($this->generateBy('asset', $asset));
    }

    /**
     * Generate an image.
     *
     * @param $type
     * @param $item
     * @return mixed
     */
    private function generateBy($type, $item)
    {
        $method = 'generateBy'.ucfirst($type);

        try {
            return $this->generator->$method($item, $this->request->all());
        } catch (FileNotFoundException|UnableToReadFile $e) {
            throw new NotFoundHttpException;
        }
    }

    /**
     * Create a response.
     *
     * @param  string  $path  Path of the generated image
     * @return mixed
     */
    private function createResponse($path)
    {
        return $this->server->getResponseFactory()->create($this->server->getCache(), $path);
    }

    /**
     * Validate the signature, if applicable.
     *
     * @return void
     */
    private function validateSignature()
    {
        // If secure images aren't enabled, don't bother validating the signature.
        if (! Config::get('statamic.assets.image_manipulation.secure')) {
            return;
        }

        $path = Str::after($this->request->url(), Site::current()->absoluteUrl());

        try {
            SignatureFactory::create(Config::getAppKey())->validateRequest($path, $this->request->query->all());
        } catch (SignatureException $e) {
            abort(400, $e->getMessage());
        }
    }
}
