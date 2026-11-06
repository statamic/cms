<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\AssetContainer;
use Statamic\Facades\Path;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class AssetContainersStore extends BasicStore
{
    public function key()
    {
        return 'asset-containers';
    }

    public function getItemFilter(SplFileInfo $file)
    {
        if (! parent::getItemFilter($file)) {
            return false;
        }

        if (! config('statamic.assets.meta_as_content')) {
            return true;
        }

        // Asset metadata is stored in subdirectories of the same directory by default,
        // so only the files in the root can be asset containers (ie. no slashes).
        $filename = Str::after(Path::tidy($file->getPathName()), $this->directory);

        return substr_count($filename, '/') === 0;
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
            ->searchIndex(Arr::get($data, 'search_index'))
            ->sortField(Arr::get($data, 'sort_by'))
            ->sortDirection(Arr::get($data, 'sort_dir'))
            ->validationRules(Arr::get($data, 'validate'));
    }
}
