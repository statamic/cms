<?php

namespace Statamic\Stache\Stores;

use Statamic\API\File;
use Statamic\API\Site;
use Statamic\API\YAML;
use Statamic\API\GlobalSet;
use Statamic\API\Collection;
use Statamic\Data\Globals\LocalizedGlobalSet;
use Statamic\Contracts\Data\Globals\GlobalSet as GlobalsContract;

class GlobalsStore extends BasicStore
{
    public function key()
    {
        return 'globals';
    }

    public function getItemsFromCache($cache)
    {
        $globals = collect();

        foreach ($cache as $id => $item) {
            $set = $globals->get($id) ?? GlobalSet::make()->id($id);

            foreach ($item['localizations'] as $site => $attributes) {
                $localized = (new LocalizedGlobalSet)
                    ->id($id)
                    ->locale($site)
                    ->handle($attributes['handle'])
                    ->initialPath($attributes['path'])
                    ->data($attributes['data']);

                $set->addLocalization($localized);
            }

            $globals[$id] = $set;
        }

        return $globals;
    }

    public function createItemFromFile($path, $contents)
    {
        $site = Site::default()->handle();
        $handle = str_after($path, $this->directory);

        if (Site::hasMultiple()) {
            list($site, $handle) = explode('/', $handle);
        }

        $handle = str_before($handle, '.yaml');

        $data = YAML::parse($contents);

        $localized = (new LocalizedGlobalSet)
            ->id($id = array_pull($data, 'id'))
            ->title(array_pull($data, 'title'))
            ->locale($site)
            ->handle($handle)
            ->initialPath($path)
            ->data($data);

        if (! $set = $this->getItem($id)) {
            $set = GlobalSet::make()->id($id);
        }

        $set->addLocalization($localized);

        return $set;
    }

    public function getItemKey($item, $path)
    {
        return $item->id();
    }

    public function filter($file)
    {
        return $file->getExtension() === 'yaml';
    }

    public function getIdByHandle($handle)
    {
        return $this->paths->map(function ($path) {
            return pathinfo($path, PATHINFO_FILENAME);
        })->flip()->get($handle);
    }

    public function save($global)
    {
        File::put($path = $global->path(), $global->fileContents());

        // TODO:
        // if (($initial = $global->initialPath()) && $path !== $initial) {
        //     File::delete($global->initialPath());
        // }
    }
}
