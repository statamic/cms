<?php

namespace Statamic\Imaging;

use League\Glide\Server;
use Statamic\Contracts\Assets\Asset;
use Statamic\Facades\Asset as Assets;
use Statamic\Facades\URL;
use Statamic\Support\Str;

class GlideCachePathResolver
{
    public function __construct(private Server $server)
    {
    }

    public function resolveForAsset(Asset $asset, array $params): string
    {
        return $this->resolve(
            $asset->basename(),
            $params,
            sourcePathPrefix: $asset->folder(),
            cachePathPrefix: ImageGenerator::assetCachePathPrefix($asset).'/'.$asset->folder(),
            asset: $asset,
        );
    }

    public function resolveForPath(string $path, array $params): string
    {
        return $this->resolve(
            $path,
            $params,
            sourcePathPrefix: '/',
            cachePathPrefix: 'paths',
        );
    }

    public function resolveForUrl(string $url, array $params): string
    {
        $parsed = app(RemoteUrlValidator::class)->parse($url);
        $qs = $parsed['query'];
        $path = $parsed['path'].($qs ? '?'.$qs : '');

        return $this->resolve(
            $path,
            $params,
            sourcePathPrefix: '/',
            cachePathPrefix: 'http',
        );
    }

    public function resolveForItem($item, array $params): string
    {
        if ($item instanceof Asset) {
            return $this->resolveForAsset($item, $params);
        }

        if (is_string($item) && Str::contains($item, '::')) {
            $asset = Assets::find($item);

            if ($asset) {
                return $this->resolveForAsset($asset, $params);
            }
        }

        if (is_string($item) && URL::isAbsolute($item)) {
            return $this->resolveForUrl($item, $params);
        }

        return $this->resolveForPath($item, $params);
    }

    private function resolve(string $image, array $params, string $sourcePathPrefix, string $cachePathPrefix, ?Asset $asset = null): string
    {
        $origSourcePrefix = $this->server->getSourcePathPrefix();
        $origCachePrefix = $this->server->getCachePathPrefix();
        $origDefaults = $this->server->getDefaults();

        $this->server->setSourcePathPrefix($sourcePathPrefix);
        $this->server->setCachePathPrefix($cachePathPrefix);
        $this->server->setDefaults(ImageGenerator::getDefaultManipulations($asset));

        try {
            return $this->server->getCachePath($image, $params);
        } finally {
            $this->server->setSourcePathPrefix($origSourcePrefix);
            $this->server->setCachePathPrefix($origCachePrefix);
            $this->server->setDefaults($origDefaults);
        }
    }
}
