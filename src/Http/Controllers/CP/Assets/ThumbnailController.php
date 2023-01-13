<?php

namespace Statamic\Http\Controllers\CP\Assets;

use Illuminate\Support\Facades\Cache;
use League\Glide\Server;
use Statamic\Facades\Asset;
use Statamic\Facades\Config;
use Statamic\Facades\Image;
use Statamic\Http\Controllers\Controller;
use Statamic\Imaging\ImageGenerator;
use Statamic\Statamic;

class ThumbnailController extends Controller
{
    /**
     * @var Server
     */
    protected $server;

    /**
     * @var ImageGenerator
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
    protected $orientation;

    /**
     * @var string
     */
    protected $mutex;

    /**
     * @param  Server  $server
     * @param  ImageGenerator  $generator
     */
    public function __construct(Server $server, ImageGenerator $generator)
    {
        $this->server = $server;
        $this->generator = $generator;
    }

    /**
     * Display the thumbnail.
     *
     * @param  string  $asset
     * @param  string  $size
     * @param  string  $orientation
     * @return \Illuminate\Http\Response
     */
    public function show($asset, $size = null, $orientation = null)
    {
        $this->size = $size;
        $this->orientation = $orientation;
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
     * @param  string  $asset  An encoded asset ID from the URL.
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

        Cache::put($this->mutex(), true, now()->addMinutes(5));

        try {
            $preset = $this->getPreset();

            if (! collect(Image::cpManipulationPresets())->has($preset)) {
                throw new \Exception('Invalid preset');
            }

            $path = $this->generator->generateByAsset(
                $this->asset,
                $this->size ? ['p' => $preset] : []
            );
        } finally {
            Cache::forget($this->mutex());
        }

        return $path;
    }

    /**
     * Get control panel thumbnail image preset name.
     *
     * Statamic has few control panel specific image presets
     *
     * @see \Statamic\Imaging\Manager::cpManipulationPresets
     *
     * @return string
     */
    private function getPreset()
    {
        return "cp_thumbnail_{$this->size}_{$this->getOrientation()}";
    }

    /**
     * Get orientation override from URL path or directly from asset.
     *
     * @return string|null
     */
    private function getOrientation()
    {
        return $this->orientation ?? $this->asset->orientation();
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

        return $this->mutex = 'imagegenerator::generating.'.md5($this->asset->id().$this->size);
    }

    /**
     * If an image is deemed too large for thumbnail generation, we'll give it a placeholder icon.
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    private function getPlaceholderResponse()
    {
        $maxWidth = Config::get('statamic.assets.thumbnails.max_width');
        $maxHeight = Config::get('statamic.assets.thumbnails.max_height');

        if ($this->asset->width() < $maxWidth && $this->asset->height() < $maxHeight) {
            return;
        }

        return redirect(Statamic::cpAssetUrl('svg/filetypes/picture.svg'));
    }
}
