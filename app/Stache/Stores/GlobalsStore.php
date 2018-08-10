<?php

namespace Statamic\Stache\Stores;

use Statamic\API\YAML;
use Statamic\API\GlobalSet;
use Statamic\API\Collection;
use Statamic\Contracts\Data\Globals\GlobalSet as GlobalsContract;

class GlobalsStore extends BasicStore
{
    public function key()
    {
        return 'globals';
    }

    public function getItemsFromCache($cache)
    {
        // TODO: TDD. This was just copy/pasted from v2.
        return $cache->map(function ($item, $id) {
            $attr = $item['attributes'];

            // Get the data for the default locale. Remove the ID since
            // we already have it and will be setting it separately.
            $data = $item['data'][default_locale()];
            unset($data['id']);

            $set = GlobalSet::create($attr['slug'])
                ->id($id)
                ->with($data)
                ->get();

            // If the set has additional locale data, add them.
            if (count($item['data']) > 1) {
                foreach ($item['data'] as $locale => $data) {
                    $set->dataForLocale($locale, $data);
                }

                $set->syncOriginal();
            }

            return $set;
        });
    }

    public function createItemFromFile($path, $contents)
    {
        $handle = pathinfo($path)['filename'];

        return GlobalSet::create($handle)
            ->with(YAML::parse($contents))
            ->get();
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

    public function save(GlobalsContract $global)
    {
        $data = [];

        $data = collect($global->locales())->mapWithKeys(function ($locale) use ($global) {
            return [$locale => $global->in($locale)->data()];
        });

        $default = $data->pull($data->keys()->first());

        // Remove any localized data that's the same as what's in the default locale.
        $data = $data->map(function ($localized) use ($default) {
            return collect($localized)->reject(function ($value, $key) use ($default) {
                return $value === array_get($default, $key);
            })->all();
        });

        // We want the default locale's data to be at the top level, and all the
        // subsequent locales to be nested under their key.
        $data = collect($default)->merge($data);

        // TODO: Change ->slug() to ->handle()
        // TODO: Let the GlobalSet object output the path, if one is already set.
        // It's possible that an existing file was saved in a subdirectory, for example.
        // We'll want to maintain that.
        $path = $this->directory . '/' . $global->slug() . '.yaml';

        $this->files->put($path, YAML::dump($data->all()));
    }
}
