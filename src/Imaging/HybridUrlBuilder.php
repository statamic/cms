<?php

namespace Statamic\Imaging;

use Exception;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Asset as Assets;
use Statamic\Facades\Glide;
use Statamic\Facades\URL;
use Statamic\Support\Str;

class HybridUrlBuilder extends ImageUrlBuilder
{
    protected GlideCachePathResolver $resolver;

    protected array $options;

    public function __construct(GlideCachePathResolver $resolver, array $options = [])
    {
        $this->resolver = $resolver;
        $this->options = $options;
    }

    /**
     * Build the URL.
     *
     * @param  \Statamic\Contracts\Assets\Asset|string  $item
     * @param  array  $params
     * @return string
     *
     * @throws \Exception
     */
    public function build($item, $params)
    {
        $this->item = $item;

        if (isset($params['mark']) && $params['mark'] instanceof Asset) {
            $asset = $params['mark'];
            $params['mark'] = 'asset::'.Str::toBase64Url($asset->containerId().'/'.$asset->path());
        }

        $cachePath = $this->resolver->resolveForItem($item, $params);

        $this->cacheSource($cachePath, $params);

        $urlPath = URL::tidy($this->options['route'].'/'.$cachePath, withTrailingSlash: false);

        return URL::makeRelative(URL::prependSiteUrl($urlPath));
    }

    private function cacheSource(string $cachePath, array $params): void
    {
        $mapping = match ($this->itemType()) {
            'asset' => ['type' => 'asset', 'id' => $this->item->id(), 'params' => $params],
            'url' => ['type' => 'url', 'url' => $this->item, 'params' => $params],
            'id' => ['type' => 'asset', 'id' => $this->item, 'params' => $params],
            'path' => ['type' => 'path', 'path' => $this->item, 'params' => $params],
            default => throw new Exception('Cannot build a hybrid Glide URL without a URL, path, or asset.'),
        };

        $mappingKey = 'hybrid::'.$cachePath;

        Glide::cacheStore()->forever($mappingKey, $mapping);

        // Add to the asset manifest so clearAsset() cleans up the mapping too.
        if ($mapping['type'] === 'asset') {
            $manifestKey = ImageGenerator::assetCacheManifestKey(
                Assets::find($mapping['id'])
            );

            $manifest = Glide::cacheStore()->get($manifestKey, []);
            $manifest[] = $mappingKey;
            Glide::cacheStore()->forever($manifestKey, array_unique($manifest));
        }
    }
}
