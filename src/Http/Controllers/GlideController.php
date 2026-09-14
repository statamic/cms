<?php

namespace Statamic\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use League\Flysystem\UnableToReadFile;
use League\Glide\Server;
use League\Glide\Signatures\SignatureException;
use League\Glide\Signatures\SignatureFactory;
use Statamic\Exceptions\InvalidRemoteUrlException;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Facades\Asset;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Config;
use Statamic\Facades\Glide;
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
     */
    public function __construct(Server $server, Request $request, ImageGenerator $generator)
    {
        $this->server = $server;
        $this->request = $request;
        $this->generator = $generator;
    }

    /**
     * Generate a manipulated image by a path.
     *
     * @param  string  $path
     * @return mixed
     */
    public function generateByPath($path)
    {
        if (Glide::isUsingHybridCaching()) {
            return $this->generateOnDemand($path);
        }

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

        $url = Str::fromBase64Url($url);

        return $this->createResponse($this->generateBy('url', $url));
    }

    /**
     * Generate an on-demand image for the hybrid caching strategy.
     *
     * The URL path is the predicted cache path. A mapping stored in the
     * Glide cache store links it back to the source and manipulation params.
     */
    private function generateOnDemand(string $path)
    {
        if (Glide::cacheDisk()->exists($path)) {
            Log::debug('Glide hybrid cache loaded ['.$path.'] If you are seeing this, your server rewrite rules have not been set up correctly.');

            return $this->createResponse($path);
        }

        $mapping = Glide::cacheStore()->get('hybrid::'.$path);

        throw_unless($mapping, new NotFoundHttpException);

        $type = $mapping['type'];
        $params = $mapping['params'];

        $item = match ($type) {
            'asset' => Asset::find($mapping['id']) ?? throw new NotFoundHttpException,
            'url' => $mapping['url'],
            'path' => $mapping['path'],
        };

        return $this->createResponse($this->ensureGenerated($type, $item, $params));
    }

    /**
     * Forget any stale cache store entry, then generate the image.
     *
     * In hybrid mode, the file on disk is the source of truth.
     * If we're here, the file doesn't exist, so the cache store
     * entry (if any) is stale and should be cleared first.
     */
    private function ensureGenerated(string $type, $item, array $params)
    {
        Glide::cacheStore()->forget(ImageGenerator::manipulationCacheKey($type, $item, $params));

        return $this->generateBy($type, $item, $params);
    }

    /**
     * Generate a manipulated image by an asset reference.
     *
     * @param  string  $encoded
     * @return mixed
     *
     * @throws \Exception
     */
    public function generateByAsset($encoded)
    {
        $this->validateSignature();

        $decoded = Str::fromBase64Url($encoded);

        // The string before the first slash is the container
        [$container, $path] = explode('/', $decoded, 2);

        throw_unless($container = AssetContainer::find($container), new NotFoundHttpException);

        throw_unless($asset = $container->asset($path), new NotFoundHttpException);

        return $this->createResponse($this->generateBy('asset', $asset));
    }

    /**
     * Generate an image.
     *
     * @return mixed
     */
    private function generateBy($type, $item, ?array $params = null)
    {
        $method = 'generateBy'.ucfirst($type);

        try {
            return $this->generator->$method($item, $params ?? $this->request->all());
        } catch (InvalidRemoteUrlException $e) {
            abort(400, $e->getMessage());
        } catch (UnableToReadFile $e) {
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
