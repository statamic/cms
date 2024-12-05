<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\AssetContainer;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;

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
            ->disk(Arr::get($data, 'disk'))
            ->title(Arr::get($data, 'title'))
            ->allowDownloading(Arr::get($data, 'allow_downloading'))
            ->allowMoving(Arr::get($data, 'allow_moving'))
            ->allowRenaming(Arr::get($data, 'allow_renaming'))
            ->allowUploads(Arr::get($data, 'allow_uploads'))
            ->createFolders(Arr::get($data, 'create_folders'))
            ->sourcePreset(Arr::get($data, 'source_preset'))
            ->warmPresets(Arr::get($data, 'warm_presets'))
            ->searchIndex(Arr::get($data, 'search_index'))
            ->sortField(Arr::get($data, 'sort_by'))
            ->sortDirection(Arr::get($data, 'sort_dir'))
            ->validationRules(Arr::get($data, 'validate'));
    }
}
