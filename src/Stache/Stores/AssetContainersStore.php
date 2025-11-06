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
            ->sourcePreset(Arr::get($data, 'source_preset'))
            ->warmPresets(Arr::get($data, 'warm_presets'))
            ->warmPresetsPerPath(Arr::get($data, 'warm_presets_per_path'))
            ->searchIndex(Arr::get($data, 'search_index'))
            ->sortField(Arr::get($data, 'sort_by'))
            ->sortDirection(Arr::get($data, 'sort_dir'))
            ->validationRules(Arr::get($data, 'validate'));
    }
}
