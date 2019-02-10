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

    public function setItem($key, $item)
    {
        if ($item instanceof LocalizedGlobalSet) {
            $item = $item->localizable();
        }

        return parent::setItem($key, $item);
    }

    public function getItemsFromCache($cache)
    {
        $globals = collect();

        foreach ($cache as $id => $item) {
            $set = $globals->get($id) ?? GlobalSet::make()
                ->id($id)
                ->handle($item['handle'])
                ->title($item['title'])
                ->blueprint($item['blueprint'])
                ->sites($item['sites'])
                ->initialPath($item['path']);

            foreach ($item['localizations'] as $site => $attributes) {
                $localized = (new LocalizedGlobalSet)
                    ->id($id)
                    ->locale($site)
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
        $data = YAML::parse($contents);
        $relative = str_after($path, $this->directory);
        $handle = str_before($relative, '.yaml');

        return Site::hasMultiple()
            ? $this->createMultiSiteGlobalFromFile($handle, $path, $data)
            : $this->createSingleSiteGlobalFromFile($handle, $path, $data);
    }

    protected function createSingleSiteGlobalFromFile($handle, $path, $data)
    {
        $set = $this->createBaseGlobalFromFile($handle, $path, $data);

        $localized = (new LocalizedGlobalSet)
            ->id($set->id())
            ->locale(Site::default()->handle())
            ->initialPath($path)
            ->data($data['data'] ?? []);

        return $set->addLocalization($localized);
    }

    protected function createMultiSiteGlobalFromFile($handle, $path, $data)
    {
        return substr_count($handle, '/') === 0
            ? $this->createBaseGlobalFromFile($handle, $path, $data)
            : $this->createLocalizedGlobalFromFile($handle, $path, $data);
    }

    protected function createBaseGlobalFromFile($handle, $path, $data)
    {
        $set = GlobalSet::make()
            ->id($data['id'])
            ->handle($handle)
            ->title($data['title'] ?? null)
            ->blueprint($data['blueprint'] ?? null)
            ->sites($data['sites'] ?? null)
            ->initialPath($path);

        // If the base set file was modified, its localizations will already exist in the Stache.
        // We should get those existing localizations and add it to this newly created set.
        // Otherwise, the localizations would just disappear since they'd no longer be linked.
        $existing = $this->items->first(function ($global) use ($handle) {
            return $global->handle() === $handle;
        });

        if ($existing) {
            $existing->localizations()->each(function ($localization) use ($set) {
                $set->addLocalization($localization);
            });
        }

        return $set;
    }

    protected function createLocalizedGlobalFromFile($handle, $path, $data)
    {
        list($site, $handle) = explode('/', $handle);

        $set = $this->items->first(function ($global) use ($handle) {
            return $global->handle() === $handle;
        });

        $localized = (new LocalizedGlobalSet)
            ->id($set->id())
            ->locale($site)
            ->initialPath($path)
            ->data($data);

        return $set->addLocalization($localized);
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
        return $this->paths->flatMap(function ($paths, $site) {
            return $paths->mapWithKeys(function ($path, $id) {
                $handle = pathinfo($path, PATHINFO_FILENAME);
                return [$handle => $id];
            });
        })->get($handle);
    }

    public function save($global)
    {
        if ($global instanceof LocalizedGlobalSet) {
            $global = $global->localizable();
        }

        $this->write($global);

        // When using multiple sites, the global's localized data exists
        // in separate files, so we'll write each one of those, too.
        if (Site::hasMultiple()) {
            $global->localizations()->each(function ($localization) {
                $this->write($localization);
            });
        }
    }

    protected function write($global)
    {
        File::put($path = $global->path(), $global->fileContents());

        // TODO:
        // if (($initial = $global->initialPath()) && $path !== $initial) {
        //     File::delete($global->initialPath());
        // }
    }
}
