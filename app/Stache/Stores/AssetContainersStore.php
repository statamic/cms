<?php

namespace Statamic\Stache\Stores;

use Statamic\API\File;
use Statamic\API\YAML;
use Statamic\Assets\Asset;
use Statamic\API\Collection;
use Statamic\API\AssetContainer;
use Statamic\Contracts\Assets\AssetContainer as ContainerContract;

class AssetContainersStore extends BasicStore
{
    public function key()
    {
        return 'asset-containers';
    }

    public function getItemsFromCache($cache)
    {
        return $cache->map(function ($item, $handle) {
            return $this->containerFromArray($handle, $item);
        });
    }

    public function createItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::parse($contents);
        $driver = array_get($data, 'driver', 'local');

        return $this->containerFromArray($handle, $data);
    }

    protected function containerFromArray($handle, $data)
    {
        $container = AssetContainer::make($handle)
            ->disk(array_get($data, 'disk'))
            ->title(array_get($data, 'title'))
            ->blueprint(array_get($data, 'blueprint'));

        foreach (array_get($data, 'assets', []) as $path => $data) {
            $container->addAsset((new Asset)->path($path)->data($data));
        }

        return $container;
    }

    public function getItemKey($item, $path)
    {
        return pathinfo($path, PATHINFO_FILENAME);
    }

    public function filter($file)
    {
        return $file->getExtension() === 'yaml';
    }

    public function save($container)
    {
        File::put($container->path(), $container->fileContents());
    }
}
