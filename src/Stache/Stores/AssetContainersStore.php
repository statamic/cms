<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\File;
use Statamic\Facades\YAML;
use Statamic\Assets\Asset;
use Statamic\Facades\Collection;
use Statamic\Facades\AssetContainer;
use Statamic\Contracts\Assets\AssetContainer as ContainerContract;

class AssetContainersStore extends BasicStore
{
    public function key()
    {
        return 'asset-containers';
    }

    public function makeItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::file($path)->parse($contents);

        return AssetContainer::make($handle)
            ->disk(array_get($data, 'disk'))
            ->title(array_get($data, 'title'))
            ->blueprint(array_get($data, 'blueprint'))
            ->allowDownloading(array_get($data, 'allow_downloading'))
            ->allowMoving(array_get($data, 'allow_moving'))
            ->allowRenaming(array_get($data, 'allow_renaming'))
            ->allowUploads(array_get($data, 'allow_uploads'))
            ->createFolders(array_get($data, 'create_folders'))
            ->searchIndex(array_get($data, 'search_index'));
    }
}
