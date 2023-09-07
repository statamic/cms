<?php

namespace Statamic\Assets;

use Statamic\Contracts\Assets\Asset;
use Statamic\Contracts\Assets\AssetRepository as Contract;
use Statamic\Contracts\Assets\QueryBuilder;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\URL;
use Statamic\Support\Str;

class AssetRepository implements Contract
{
    public function all()
    {
        return AssetCollection::make(AssetContainer::all()->flatMap(function ($container) {
            return $container->assets();
        }));
    }

    public function whereContainer(string $container)
    {
        return AssetContainer::find($container)->assets();
    }

    public function whereFolder(string $folder, string $container)
    {
        return AssetContainer::find($container)->assets($folder);
    }

    public function find(string $asset)
    {
        return Str::contains($asset, '::')
            ? $this->findById($asset)
            : $this->findByUrl($asset);
    }

    public function findByUrl(string $url)
    {
        // If a container can't be resolved, we'll assume there's no asset.
        if (! $container = $this->resolveContainerFromUrl($url)) {
            return null;
        }

        $siteUrl = rtrim(Site::current()->absoluteUrl(), '/');
        $containerUrl = $container->url();

        if (starts_with($containerUrl, '/')) {
            $containerUrl = $siteUrl.$containerUrl;
        }

        if (starts_with($containerUrl, $siteUrl)) {
            $url = $siteUrl.$url;
        }

        $path = str_after($url, $containerUrl);

        return $container->asset($path);
    }

    protected function resolveContainerFromUrl($url)
    {
        return AssetContainer::all()->sortByDesc(function ($container) {
            return strlen($container->url());
        })->first(function ($container, $id) use ($url) {
            return starts_with($url, $container->url())
                || starts_with(URL::makeAbsolute($url), $container->url());
        });
    }

    public function whereUrl($url)
    {
        return $this->findByUrl($url); // TODO: Replace usages with findByUrl
    }

    public function findById(string $id)
    {
        [$container_id, $path] = explode('::', $id);

        // If a container can't be found, we'll assume there's no asset.
        if (! $container = AssetContainer::find($container_id)) {
            return null;
        }

        return $container->asset($path);
    }

    public function whereId($id)
    {
        return $this->findById($id); // TODO: Replace usages with findById
    }

    public function findByPath(string $path)
    {
        return $this->all()->filter(function ($asset) use ($path) {
            return $asset->resolvedPath() === $path;
        })->first();
    }

    public function wherePath($path)
    {
        return $this->findByPath($path); // TODO: Replace usages with findByPath
    }

    public function make()
    {
        return app(Asset::class);
    }

    public function query()
    {
        return app(QueryBuilder::class);
    }

    public function save($asset)
    {
        $store = Stache::store('assets::'.$asset->containerHandle());

        $cache = $asset->container()->contents();

        $cache->add($asset->path());

        if ($asset->path() !== ($originalPath = $asset->getOriginal('path'))) {
            $originalId = $asset->container()->handle().'::'.$originalPath;
            $store->delete($store->getItem($originalId));
            $cache->forget($originalPath);
        }

        $cache->save();

        $store->save($asset);

        $asset->writeMeta($asset->generateMeta());
    }

    public function delete($asset)
    {
        $asset->container()->contents()->forget($asset->path())->save();

        Stache::store('assets::'.$asset->containerHandle())->delete($asset);
    }

    public static function bindings(): array
    {
        return [
            Asset::class => \Statamic\Assets\Asset::class,
            QueryBuilder::class => \Statamic\Assets\QueryBuilder::class,
        ];
    }
}
