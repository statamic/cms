<?php

namespace Statamic\Http\Controllers;

use Statamic\API\Asset;
use Statamic\API\Cache;
use Statamic\API\Config;
use League\Glide\Server;
use Statamic\Imaging\ImageGenerator;

class AssetThumbnailController extends Controller
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @var Generator
     */
    protected $generator;

    /**
     * @var \Statamic\Contracts\Assets\Asset
     */
    protected $asset;

    /**
     * @var string
     */
    protected $size;

    /**
     * @var string
     */
    protected $mutex;

    /**
     * @param Server         $server
     * @param ImageGenerator $generator
     */
    public function __construct(Server $server, ImageGenerator $generator)
    {
        $this->server = $server;
        $this->generator = $generator;
    }

    /**
     * Display the thumbnail.
     *
     * @param  string $asset
     * @param  string $size
     * @return \Illuminate\Http\Response
     */
    public function show($asset, $size = null)
    {
        $this->size = $size;
        $this->asset = $this->asset($asset);

        if ($placeholder = $this->getPlaceholderResponse()) {
            return $placeholder;
        }

        return $this->server->getResponseFactory()->create(
            $this->server->getCache(),
            $this->generate()
        );
    }

    /**
     * Get an asset, or throw a 404 if not found.
     *
     * @param  string $asset  An encoded asset ID from the URL.
     * @return \Statamic\Contracts\Assets\Asset
     */
    private function asset($asset)
    {
        if (! $asset = Asset::find(base64_decode($asset))) {
            abort(404);
        }

        return $asset;
    }

    /**
     * Generate the image.
     *
     * @return string
     */
    private function generate()
    {
        $this->waitIfProcessing();

        Cache::put($this->mutex(), true, 120);

        $path = $this->generator->generateByAsset(
            $this->asset,
            $this->size ? ['p' => "cp_thumbnail_{$this->size}"] : []
        );

        Cache::forget($this->mutex());

        return $path;
    }

    /**
     * If the image is being processed in a different request, just hold on for a moment.
     *
     * @return void
     */
    private function waitIfProcessing()
    {
        while ($cache = Cache::get($this->mutex())) {
            sleep(1);
        }
    }

    /**
     * Get the cache key of the mutex.
     *
     * @return string
     */
    private function mutex()
    {
        if ($this->mutex) {
            return $this->mutex;
        }

        return $this->mutex = 'imagegenerator::generating.' . md5($this->asset->id() . $this->size);
    }

    /**
     * If an image is deemed too large for thumbnail generation, we'll give it a placeholder icon.
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    private function getPlaceholderResponse()
    {
        $maxWidth = Config::get('assets.thumbnail_max_width');
        $maxHeight = Config::get('assets.thumbnail_max_height');

        if ($this->asset->width() < $maxWidth && $this->asset->height() < $maxHeight) {
            return;
        }

        return redirect(cp_resource_url('img/filetypes/' . $this->asset->extension() . '.png'));
    }
}
