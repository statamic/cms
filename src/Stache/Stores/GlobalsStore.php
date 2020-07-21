<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\File;
use Statamic\Facades\GlobalSet;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class GlobalsStore extends BasicStore
{
    public function key()
    {
        return 'globals';
    }

    public function getItemFilter(SplFileInfo $file)
    {
        // The global sets themselves should only exist in the root
        // (ie. no slashes in the filename)
        $filename = str_after(Path::tidy($file->getPathName()), $this->directory);

        return substr_count($filename, '/') === 0 && $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $relative = str_after($path, $this->directory);
        $handle = str_before($relative, '.yaml');

        // If it's a variables file that was requested, instead assume that the
        // base file was requested. The variables will get made as part of it.
        if (Site::hasMultiple() && Str::contains($relative, '/')) {
            $handle = pathinfo($relative, PATHINFO_FILENAME);
            $path = $this->directory.$handle.'.yaml';
            $data = YAML::file($path)->parse();

            return $this->makeMultiSiteGlobalFromFile($handle, $path, $data);
        }

        $data = YAML::file($path)->parse($contents);

        return Site::hasMultiple()
            ? $this->makeMultiSiteGlobalFromFile($handle, $path, $data)
            : $this->makeSingleSiteGlobalFromFile($handle, $path, $data);
    }

    protected function makeSingleSiteGlobalFromFile($handle, $path, $data)
    {
        $set = $this->makeBaseGlobalFromFile($handle, $path, $data);

        return $set->addLocalization(
            $set
                ->makeLocalization(Site::default()->handle())
                ->initialPath($path)
                ->data($data['data'] ?? [])
        );
    }

    protected function makeMultiSiteGlobalFromFile($handle, $path, $data)
    {
        $set = $this->makeBaseGlobalFromFile($handle, $path, $data);

        Site::all()->filter(function ($site) use ($handle) {
            return File::exists($this->directory.$site->handle().'/'.$handle.'.yaml');
        })->map->handle()->map(function ($site) use ($set) {
            return $this->makeVariables($set, $site);
        })->filter()->each(function ($variables) use ($set) {
            $set->addLocalization($variables);
        });

        return $set;
    }

    protected function makeBaseGlobalFromFile($handle, $path, $data)
    {
        return GlobalSet::make()
            ->handle($handle)
            ->title($data['title'] ?? null)
            ->initialPath($path);
    }

    protected function makeVariables($set, $site)
    {
        $variables = $set->makeLocalization($site);

        // todo: cache the reading and parsing of the file
        if (! File::exists($path = $variables->path())) {
            return;
        }
        $data = YAML::file($path)->parse();

        $variables
            ->initialPath($path)
            ->data(Arr::except($data, 'origin'));

        if ($origin = Arr::get($data, 'origin')) {
            $variables->origin($origin);
        }

        return $variables;
    }

    protected function getKeyFromPath($path)
    {
        if ($key = parent::getKeyFromPath($path)) {
            return $key;
        }

        // If we're not using multiple sites and no key has been
        // found at this point, then we aren't going to find one.
        if (! Site::hasMultiple()) {
            return null;
        }

        // Given a path to a variables file, get the key based on its base global set path.
        if (Str::contains($relative = str_after($path, $this->directory), '/')) {
            $handle = pathinfo($relative, PATHINFO_FILENAME);
            $path = $this->directory.$handle.'.yaml';

            return $this->paths()->flip()->get($path);
        }
    }

    public function save($set)
    {
        parent::save($set);

        if (Site::hasMultiple()) {
            Site::all()->each(function ($site) use ($set) {
                $site = $site->handle();
                $set->existsIn($site) ? $set->in($site)->writeFile() : $set->makeLocalization($site)->deleteFile();
            });
        }
    }
}
