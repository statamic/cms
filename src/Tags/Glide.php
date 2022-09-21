<?php

namespace Statamic\Tags;

use Facades\Statamic\Imaging\Attributes;
use League\Glide\Server;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Facades\Asset;
use Statamic\Facades\Compare;
use Statamic\Facades\Config;
use Statamic\Facades\Glide as GlideManager;
use Statamic\Facades\Image;
use Statamic\Facades\Path;
use Statamic\Facades\URL;
use Statamic\Imaging\ImageGenerator;
use Statamic\Support\Str;

class Glide extends Tags
{
    /**
     * Maps to {{ glide:[field] }}.
     *
     * Where `field` is the variable containing the image ID
     *
     * @param  string  $method
     * @param  array  $args
     * @return string
     */
    public function __call($method, $args)
    {
        $tag = explode(':', $this->tag, 2)[1];

        $item = $this->context->value($tag);

        if ($this->isPair) {
            return $this->generate($item);
        }

        return $this->output($this->generateGlideUrl($item));
    }

    /**
     * Maps to {{ glide }}.
     *
     * Alternate syntax, where you pass the ID or path directly as a parameter or tag pair content
     *
     * @return string
     */
    public function index()
    {
        if ($this->isPair) {
            return $this->generate();
        }

        $item = $this->params->get(['src', 'id', 'path']);

        return $this->output($this->generateGlideUrl($item));
    }

    /**
     * Maps to {{ glide:batch }}.
     *
     * A tag pair that converts all image URLs to Glide URLs.
     *
     * @return string
     */
    public function batch()
    {
        $content = $this->parse([]);

        preg_match_all('/<img[^>]*src="([^"]*)"/i', $content, $matches, PREG_SET_ORDER);

        if (empty($matches)) {
            return $content;
        }

        $matches = collect($matches)->map(function ($match) {
            return [
                $match[0],
                sprintf('<img src="%s"', $this->generateGlideUrl($match[1])),
            ];
        })->transpose();

        $content = str_replace($matches[0], $matches[1], $content);

        return $content;
    }

    /**
     * Maps to {{ glide:data_url }}.
     *
     * Converts a Glide image to a data URL.
     *
     * @return string
     */
    public function dataUrl()
    {
        $item = $this->params->get(['src', 'id', 'path']);

        return $this->output($this->generateGlideDataUrl($item));
    }

    /**
     * Maps to {{ glide:data_uri }}.
     *
     * Alias of data_url
     *
     * @return string
     */
    public function dataUri()
    {
        return $this->dataUrl();
    }

    /**
     * Maps to {{ glide:generate }} ... {{ /glide:generate }}.
     *
     * Generates the image and makes variables available within the pair.
     *
     * @return string
     */
    public function generate($items = null)
    {
        $items = $items ?? $this->params->get(['src', 'id', 'path']);

        if (Compare::isQueryBuilder($items)) {
            $items = $items->get();
        }

        $items = is_iterable($items) ? collect($items) : collect([$items]);

        return $items->map(function ($item) {
            $data = ['url' => $this->generateGlideUrl($item)];

            if ($this->isResizable($item)) {
                $path = $this->generateImage($item);
                $attrs = Attributes::from(GlideManager::cacheDisk()->getDriver(), $path);
                $data = array_merge($data, $attrs);
            }

            if ($item instanceof Augmentable) {
                $data = array_merge($item->toAugmentedArray(), $data);
            }

            return $data;
        })->all();
    }

    /**
     * Generate the image.
     *
     * @param  mixed  $item
     * @return string
     */
    private function generateImage($item)
    {
        $item = $this->normalizeItem($item);
        $params = $this->getGlideParams($item);

        if (is_string($item) && Str::isUrl($item)) {
            return Str::startsWith($item, ['http://', 'https://'])
                ? $this->getGenerator()->generateByUrl($item, $params)
                : $this->getGenerator()->generateByPath($item, $params);
        }

        return $this->getGenerator()->generateByAsset(Asset::find($item), $params);
    }

    /**
     * Output the tag.
     *
     * @param  string  $url
     * @return string
     */
    private function output($url)
    {
        if ($this->params->bool('tag')) {
            return "<img src=\"$url\" alt=\"{$this->params->get('alt')}\" />";
        }

        return $url;
    }

    /**
     * The URL generation.
     *
     * @param  string  $item  Either the ID or path of the image.
     * @return string
     */
    private function generateGlideUrl($item)
    {
        try {
            $url = $this->isResizable($item) ? $this->getManipulator($item)->build() : $this->normalizeItem($item);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return;
        }

        $url = ($this->params->bool('absolute', $this->useAbsoluteUrls())) ? URL::makeAbsolute($url) : URL::makeRelative($url);

        return $url;
    }

    /**
     * The data URL generation.
     *
     * @param  string  $item  Either the ID or path of the image.
     * @return string
     */
    private function generateGlideDataUrl($item)
    {
        $cache = GlideManager::cacheDisk();

        try {
            $path = $this->generateImage($item);
            $source = $cache->read($path);
            $url = 'data:'.$cache->mimeType($path).';base64,'.base64_encode($source);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return;
        }

        return $url;
    }

    /**
     * Get the raw Glide parameters.
     *
     * @param  string|null  $item
     * @return array
     */
    private function getGlideParams($item = null)
    {
        return $this->getManipulator($item)->getParams();
    }

    /**
     * Get the image manipulator with the parameters added to it.
     *
     * @param  string|null  $item
     * @return \Statamic\Imaging\GlideImageManipulator
     */
    private function getManipulator($item = null)
    {
        $manipulator = Image::manipulate($this->normalizeItem($item));

        $this->getManipulationParams()->each(function ($value, $param) use ($manipulator) {
            $manipulator->$param($value);
        });

        return $manipulator;
    }

    /**
     * Normalize an item to be passed into the manipulator.
     *
     * @param  string  $item  An asset ID, asset URL, or external URL.
     * @return string|Statamic\Contracts\Assets\Asset
     */
    private function normalizeItem($item)
    {
        if ($item instanceof AssetContract) {
            return $item;
        }

        // External URLs are already fine as-is.
        if (Str::startsWith($item, ['http://', 'https://'])) {
            return $item;
        }

        // Double colons indicate an asset ID.
        if (Str::contains($item, '::')) {
            return Asset::find($item);
        }

        // In a subfolder installation, the subfolder will likely be passed in
        // with the path. We don't want it in there, so we'll strip it out.
        // We'll need it to have a leading slash to be treated as a URL.
        $item = Str::ensureLeft(Str::removeLeft($item, Config::getSiteUrl()), '/');

        // In order for auto focal cropping to happen, we need to provide an
        // actual asset instance to the manipulator instead of just a URL.
        if ($asset = Asset::find($item)) {
            $item = $asset;
        }

        return $item;
    }

    /**
     * Get the tag parameters applicable to image manipulation.
     *
     * @return \Illuminate\Support\Collection
     */
    private function getManipulationParams()
    {
        $params = collect();

        foreach ($this->params as $param => $value) {
            if (! in_array($param, ['src', 'id', 'path', 'tag', 'alt', 'absolute'])) {
                $params->put($param, $value);
            }
        }

        return $params;
    }

    /**
     * Get the image generator.
     *
     * @return ImageGenerator
     */
    private function getGenerator()
    {
        return app(ImageGenerator::class);
    }

    /**
     * Get the Glide Server instance.
     *
     * @return Server
     */
    private function getServer()
    {
        return app(Server::class);
    }

    /**
     * Checks if a file at a given path is resizable.
     *
     * @param  string  $item
     * @return bool
     */
    private function isResizable($item)
    {
        return in_array(strtolower(Path::extension($item)), $this->allowedFileFormats());
    }

    /**
     * The list of allowed file formats based on the configured driver.
     *
     * @see http://image.intervention.io/getting_started/formats
     *
     * @return array
     *
     * @throws \Exception
     */
    private function allowedFileFormats()
    {
        $driver = config('statamic.assets.image_manipulation.driver');

        if ($driver == 'gd') {
            return ['jpeg', 'jpg', 'png', 'gif', 'webp'];
        } elseif ($driver == 'imagick') {
            return ['jpeg', 'jpg', 'png', 'gif', 'tif', 'bmp', 'psd', 'webp'];
        }

        throw new \Exception("Unsupported image manipulation driver [$driver]");
    }

    private function useAbsoluteUrls()
    {
        return Str::startsWith(GlideManager::url(), ['http://', 'https://']);
    }
}
