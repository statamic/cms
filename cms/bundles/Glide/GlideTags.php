<?php

namespace Statamic\Addons\Glide;

use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\Asset;
use Statamic\API\Image;
use Statamic\API\Config;
use League\Glide\Server;
use Statamic\Extend\Tags;
use Statamic\Imaging\ImageGenerator;

class GlideTags extends Tags
{
    /**
     * Maps to {{ glide:[field] }}
     *
     * Where `field` is the variable containing the image ID
     *
     * @param  $method
     * @param  $args
     * @return string
     */
    public function __call($method, $args)
    {
        $tag = explode(':', $this->tag, 2)[1];

        $item = array_get($this->context, $tag);

        return $this->output($this->generateGlideUrl($item));
    }

    /**
     * Maps to {{ glide }}
     *
     * Alternate syntax, where you pass the ID or path directly as a parameter or tag pair content
     *
     * @return string
     */
    public function index()
    {
        $item = ($this->content)
            ? $this->parse([])
            : $this->get(['src', 'id', 'path']);

        return $this->output($this->generateGlideUrl($item));
    }

    /**
     * Maps to {{ glide:batch }}
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
                sprintf('<img src="%s"', $this->generateGlideUrl($match[1]))
            ];
        })->transpose();

        $content = str_replace($matches[0], $matches[1], $content);

        return $content;
    }

    /**
     * Maps to {{ glide:generate }} ... {{ /glide:generate }}
     *
     * Generates the image and makes variables available within the pair.
     *
     * @return string
     */
    public function generate()
    {
        $item = $this->get(['src', 'id', 'path']);

        $url = $this->generateGlideUrl($item);

        $path = $this->generateImage($item);

        list($width, $height) = getimagesize($this->getServer()->getCache()->getAdapter()->getPathPrefix().$path);

        return $this->parse(
            compact('url', 'width', 'height')
        );
    }

    /**
     * Generate the image
     *
     * @param string $item  Either a path or an asset ID
     * @return string       Path to the generated image
     */
    private function generateImage($item)
    {
        $params = $this->getGlideParams($item);

        return (Str::isUrl($item))
            ? $this->getGenerator()->generateByPath($item, $params)
            : $this->getGenerator()->generateByAsset(Asset::find($item), $params);
    }

    /**
     * Output the tag
     *
     * @param string $url
     * @return string
     */
    private function output($url)
    {
        if ($this->getBool('tag')) {
            return "<img src=\"$url\" alt=\"{$this->get('alt')}\" />";
        }

        return $url;
    }

    /**
     * The URL generation
     *
     * @param  string $item  Either the ID or path of the image.
     * @return string
     */
    private function generateGlideUrl($item)
    {
        try {
            $url = $this->getManipulator($item)->build();
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return;
        }

        $url = ($this->getBool('absolute')) ? URL::makeAbsolute($url) : URL::makeRelative($url);

        return $url;
    }

    /**
     * Get the raw Glide parameters
     *
     * @param string|null $item
     * @return array
     */
    private function getGlideParams($item = null)
    {
        return $this->getManipulator($item)->getParams();
    }

    /**
     * Get the image manipulator with the parameters added to it
     *
     * @param string|null $item
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
     * @param  string $item  An asset ID, asset URL, or external URL.
     * @return string|Statamic\Contracts\Assets\Asset
     */
    private function normalizeItem($item)
    {
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
     * Get the tag parameters applicable to image manipulation
     *
     * @return \Illuminate\Support\Collection
     */
    private function getManipulationParams()
    {
        $params = collect();

        foreach ($this->parameters as $param => $value) {
            if (! in_array($param, ['src', 'id', 'path', 'tag', 'alt', 'absolute'])) {
                $params->put($param, $value);
            }
        }

        return $params;
    }

    /**
     * Get the image generator
     *
     * @return ImageGenerator
     */
    private function getGenerator()
    {
        return app(ImageGenerator::class);
    }

    /**
     * Get the Glide Server instance
     *
     * @return Server
     */
    private function getServer()
    {
        return app(Server::class);
    }
}
