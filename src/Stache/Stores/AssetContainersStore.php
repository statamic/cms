<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\AssetContainer;
use Statamic\Facades\YAML;

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
            ->allowDownloading(array_get($data, 'allow_downloading'))
            ->allowMoving(array_get($data, 'allow_moving'))
            ->allowRenaming(array_get($data, 'allow_renaming'))
            ->allowUploads(array_get($data, 'allow_uploads'))
            ->createFolders(array_get($data, 'create_folders'))
            ->sourcePreset(array_get($data, 'source_preset'))
            ->warmPresets(array_get($data, 'warm_presets'))
            ->searchIndex(array_get($data, 'search_index'))
            ->sortField(array_get($data, 'sort_by'))
            ->sortDirection(array_get($data, 'sort_dir'));
    }
}
