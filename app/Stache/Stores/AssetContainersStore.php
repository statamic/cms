<?php

namespace Statamic\Stache\Stores;

use Statamic\API\YAML;
use Statamic\API\Collection;
use Statamic\API\AssetContainer;

class AssetContainersStore extends BasicStore
{
    public function key()
    {
        return 'asset-containers';
    }

    public function createItemFromFile($path, $contents)
    {
        $id = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::parse($contents);
        $driver = array_get($data, 'driver', 'local');

        $container = AssetContainer::create();
        $container->id($id);
        $container->data(YAML::parse($contents));
        // $container->url($this->getUrl($id, $driver, $data)); // TODO: TDD

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
}
