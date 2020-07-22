<?php

namespace Statamic\Tags;

use League\Glide\Server;
use Statamic\Contracts\Data\Augmentable;
use Statamic\Facades\Asset;
use Statamic\Facades\Config;
use Statamic\Facades\Image;
use Statamic\Facades\Path;
use Statamic\Facades\URL;
use Statamic\Imaging\ImageGenerator;
use Statamic\Support\Str;

class Glide extends Tags
{
    /**
     * Allowed file extension for the gd image manipulation driver.
     * (Glide does rely on the intervention package)
     * http://image.intervention.io/getting_started/formats
     */
    const ALLOWED_FILE_FORMATS_GD = ['jpeg', 'jpg', 'png', 'gif', 'webp'];

    /**
     * Allowed file extension for the imagick image manipulation driver in combination with glide.
     * (Glide does rely on the intervention package)
     * http://image.intervention.io/getting_started/formats
     */
    const ALLOWED_FILE_FORMATS_IMAGICK = ['jpeg', 'jpg', 'png', 'gif', 'tif', 'bmp', 'psd', 'webp'];
    
    /**
     * Maps to {{ glide:[field] }}.
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

        $item = $this->get(['src', 'id', 'path']);

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
     * Maps to {{ glide:generate }} ... {{ /glide:generate }}.
     *
     * Generates the image and makes variables available within the pair.
     *
     * @return string
     */
    public function generate($items = null)
    {
        $items = $items ?? $this->get(['src', 'id', 'path']);

        $items = is_iterable($items) ? collect($items) : collect([$items]);

        return $items->map(function ($item) {
            $url = $this->generateGlideUrl($item);

            $path = $this->generateImage($item);

            [$width, $height] = getimagesize($this->getServer()->getCache()->getAdapter()->getPathPrefix().$path);

            $data = compact('url', 'width', 'height');

            if ($item instanceof Augmentable) {
                $data = array_merge($item->toAugmentedArray(), $data);
            }

            return $data;
        })->all();
    }

    /**
     * Generate the image.
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
     * Output the tag.
     *
     * @param string $url
     * @return string
     */
    private function output($url)
    {
        if ($this->isPair) {
            return $this->parse(
                compact('url', 'width', 'height')
            );
        }
        if ($this->getBool('tag')) {
            return "<img src=\"$url\" alt=\"{$this->get('alt')}\" />";
        }

        return $url;
    }

    /**
     * The URL generation.
     *
     * @param  string $item  Either the ID or path of the image.
     * @return string
     */
    private function generateGlideUrl($item)
    {
        try {
            // In case the given item extension is not supported by glide
            // and herby not resizable, the original path  will be returned.
            $url = $this->isResizable($item) ? $this->getManipulator($item)->build() : $this->normalizeItem($item);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());

            return;
        }

        $url = ($this->getBool('absolute')) ? URL::makeAbsolute($url) : URL::makeRelative($url);

        return $url;
    }

    /**
     * Get the raw Glide parameters.
     *
     * @param string|null $item
     * @return array
     */
    private function getGlideParams($item = null)
    {
        return $this->getManipulator($item)->getParams();
    }

    /**
     * Get the image manipulator with the parameters added to it.
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
     * Get the tag parameters applicable to image manipulation.
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
     * Checking against a whitelist of allowed file extensions.
     *
     * @param string $item
     * @return bool
     */
    private function isResizable($item)
    {
        return in_array(strtolower(Path::extension($item)), $this->allowedFileFormats());
    }

    /**
     * The whitelist with allowed file extensiosn will be returned,
     * depending on the chosen image manipulation driver.
     *
     * @throws \Exception
     * @return array
     */
    private function allowedFileFormats()
    {
        if (extension_loaded('gd')) {
            return self::ALLOWED_FILE_FORMATS_GD;
        }

        if (extension_loaded('imagick')) {
            return self::ALLOWED_FILE_FORMATS_IMAGICK;
        }

        throw new \Exception('To use glide, you need to have GD or Imagick installed.');
    }
}
