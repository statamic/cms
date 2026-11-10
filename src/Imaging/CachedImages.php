<?php

namespace Statamic\Imaging;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use League\Glide\Server;
use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\AssetContainer as AssetContainerContract;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Glide;
use Statamic\Support\Str;

class CachedImages
{
    public function __construct(private Server $server)
    {
    }

    public function summary(): array
    {
        $count = 0;
        $size = 0;
        $assets = [];

        foreach ($this->server->getCache()->listContents('', true) as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $count++;
            $size += $file->fileSize() ?? $this->server->getCache()->fileSize($file->path());

            if (str_starts_with($file->path(), 'containers/') && substr_count($file->path(), '/') >= 4) {
                $assets[dirname($file->path(), 2)] = true;
            }
        }

        return ['count' => $count, 'size' => $size, 'assets' => count($assets)];
    }

    public function containers(): Collection
    {
        return AssetContainer::all()->map(function ($container) {
            $stats = $this->stats($container);

            return [
                'id' => $container->id(),
                'handle' => $container->handle(),
                'title' => $container->title(),
                'count' => $stats['count'],
                'size' => $stats['size'],
                'size_formatted' => Str::fileSizeForHumans($stats['size']),
            ];
        })->sortBy('title', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    public function clear(AssetContainerContract $container): array
    {
        $before = $this->stats($container);

        $container->assets()->each(fn ($asset) => Glide::clearAsset($asset, preserveHybridMappings: true));

        $this->server->getCache()->deleteDirectory('containers/'.$container->id());

        $after = $this->stats($container);

        return [
            'count' => max(0, $before['count'] - $after['count']),
            'size' => max(0, $before['size'] - $after['size']),
        ];
    }

    public function assets(AssetContainerContract $container): Collection
    {
        $prefix = 'containers/'.$container->id().'/';
        $assets = [];

        foreach ($this->server->getCache()->listContents($prefix, true) as $file) {
            if (! $file->isFile()) {
                continue;
            }

            // Variations are stored under {asset path}/{hash}/{filename}.
            $path = dirname(Str::after($file->path(), $prefix), 2);

            if ($path === '.') {
                continue;
            }

            $assets[$path] ??= [
                'id' => $container->id().'::'.$path,
                'title' => basename($path),
                'path' => $path,
                'extension' => pathinfo($path, PATHINFO_EXTENSION),
                'thumbnail' => null,
                'thumbnail_size' => null,
                'count' => 0,
                'size' => 0,
            ];

            $size = $file->fileSize() ?? $this->server->getCache()->fileSize($file->path());
            $assets[$path]['count']++;
            $assets[$path]['size'] += $size;

            if ($assets[$path]['thumbnail_size'] === null || $size < $assets[$path]['thumbnail_size']) {
                $assets[$path]['thumbnail'] = $this->previewUrl($container, $file->path());
                $assets[$path]['thumbnail_size'] = $size;
            }
        }

        return collect($assets)->map(function ($asset) {
            $asset['size_formatted'] = Str::fileSizeForHumans($asset['size']);
            unset($asset['thumbnail_size']);

            return $asset;
        })->sortBy('path', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }

    public function variants(Asset $asset): Collection
    {
        $variants = [];
        $path = 'containers/'.$asset->containerId().'/'.$asset->path();

        foreach ($this->server->getCache()->listContents($path, true) as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $size = $file->fileSize() ?? $this->server->getCache()->fileSize($file->path());
            $generated = $file->lastModified() ?? $this->server->getCache()->lastModified($file->path());

            $variants[] = [
                'id' => $file->path(),
                'title' => basename($file->path()),
                'hash' => basename(dirname($file->path())),
                'format' => strtoupper(pathinfo($file->path(), PATHINFO_EXTENSION)),
                'size' => $size,
                'size_formatted' => Str::fileSizeForHumans($size),
                'generated_at' => Carbon::createFromTimestampUTC($generated)->toIso8601String(),
                'preview_url' => $this->previewUrl($asset->container(), $file->path()),
            ];
        }

        return collect($variants)->sortBy('id', SORT_NATURAL)->values();
    }

    public function clearAsset(Asset $asset): array
    {
        $before = $this->stats($asset->container(), $asset->path());

        Glide::clearAsset($asset, preserveHybridMappings: true);

        $after = $this->stats($asset->container(), $asset->path());

        return [
            'count' => max(0, $before['count'] - $after['count']),
            'size' => max(0, $before['size'] - $after['size']),
        ];
    }

    public function clearVariants(Asset $asset, array $paths): array
    {
        $cache = $this->server->getCache();
        $size = 0;

        foreach ($paths as $path) {
            $size += $cache->fileSize($path);
            $cache->delete($path);
        }

        $store = Glide::cacheStore();
        $manifestKey = ImageGenerator::assetCacheManifestKey($asset);
        $clearedPaths = array_fill_keys($paths, true);

        // Keep hybrid source mappings so existing URLs can regenerate the cleared variation.
        $keys = collect($store->get($manifestKey, []))->reject(function ($key) use ($store, $clearedPaths) {
            $path = $store->get($key);

            if (! is_string($path) || ! isset($clearedPaths[$path])) {
                return false;
            }

            $store->forget($key);

            return true;
        })->values()->all();

        if ($keys) {
            $store->forever($manifestKey, $keys);
        } else {
            $store->forget($manifestKey);
        }

        $remaining = $this->stats($asset->container(), $asset->path());

        return [
            'count' => count($paths),
            'size' => $size,
            'remaining' => $remaining['count'],
            'remaining_size' => $remaining['size'],
        ];
    }

    private function stats(AssetContainerContract $container, ?string $assetPath = null): array
    {
        $count = 0;
        $size = 0;
        $path = 'containers/'.$container->id().($assetPath === null ? '' : '/'.$assetPath);

        foreach ($this->server->getCache()->listContents($path, true) as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $count++;
            $size += $file->fileSize() ?? $this->server->getCache()->fileSize($file->path());
        }

        return compact('count', 'size');
    }

    private function previewUrl(AssetContainerContract $container, string $path): string
    {
        return cp_route('utilities.cache.image-preview', ['container' => $container->id(), 'path' => $path]);
    }
}
