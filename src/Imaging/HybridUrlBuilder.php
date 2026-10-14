<?php

namespace Statamic\Imaging;

use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Asset as Assets;
use Statamic\Facades\Glide;
use Statamic\Facades\URL;

class HybridUrlBuilder extends ImageUrlBuilder
{
    public function __construct(private GlideCachePathResolver $resolver, private array $options = [])
    {
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

        if ($this->itemType() === 'id') {
            $this->item = $this->findAsset($item);
        }

        $cachePath = $this->resolver->resolveForItem($this->item, $params);

        $this->cacheSource($cachePath, $this->withEncodedWatermark($params));

        $encodedCachePath = collect(explode('/', $cachePath))->map(rawurlencode(...))->implode('/');

        return URL::makeRelative($this->options['route'].'/'.$encodedCachePath);
    }

    private function findAsset(string $id): Asset
    {
        if ($asset = Assets::find($id)) {
            return $asset;
        }

        throw new AssetNotFoundException(
            sprintf('Could not generate a hybrid manipulated image URL from asset [%s]', $id)
        );
    }

    private function cacheSource(string $cachePath, array $params): void
    {
        $mapping = match ($this->itemType()) {
            'asset' => ['type' => 'asset', 'id' => $this->item->id(), 'params' => $params],
            'url' => ['type' => 'url', 'url' => $this->item, 'params' => $params],
            'path' => ['type' => 'path', 'path' => $this->item, 'params' => $params],
        };

        $mappingKey = 'hybrid::'.$cachePath;

        Glide::cacheStore()->forever($mappingKey, $mapping);

        if ($this->item instanceof Asset) {
            $this->addToAssetManifest($mappingKey);
        }
    }

    private function addToAssetManifest(string $mappingKey): void
    {
        $manifestKey = ImageGenerator::assetCacheManifestKey($this->item);

        $manifest = Glide::cacheStore()->get($manifestKey, []);
        $manifest[] = $mappingKey;

        Glide::cacheStore()->forever($manifestKey, array_unique($manifest));
    }
}
