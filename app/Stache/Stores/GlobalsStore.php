<?php

namespace Statamic\Stache\Stores;

use Statamic\API\Arr;
use Statamic\API\File;
use Statamic\API\Site;
use Statamic\API\YAML;
use Statamic\API\GlobalSet;
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
        $filename = str_after($file->getPathName(), $this->directory);
        return substr_count($filename, '/') === 0;
    }

    public function makeItemFromFile($path, $contents)
    {
        $data = YAML::parse($contents);
        $relative = str_after($path, $this->directory);
        $handle = str_before($relative, '.yaml');

        return Site::hasMultiple()
            ? $this->makeMultiSiteGlobalFromFile($handle, $path, $data)
            : $this->makeSingleSiteGlobalFromFile($handle, $path, $data);
    }

    protected function makeSingleSiteGlobalFromFile($handle, $path, $data)
    {
        $set = $this
            ->makeBaseGlobalFromFile($handle, $path, $data)
            ->sites([$site = Site::default()->handle()]);

        return $set->addLocalization(
            $set
                ->makeLocalization($site)
                ->initialPath($path)
                ->data($data['data'] ?? [])
        );
    }

    protected function makeMultiSiteGlobalFromFile($handle, $path, $data)
    {
        $set = $this->makeBaseGlobalFromFile($handle, $path, $data);

        $set->sites()->map(function ($site) use ($set) {
            return $this->makeVariables($set, $site);
        })->filter()->each(function ($variables) use ($set) {
            $set->addLocalization($variables);
        });

        return $set;
    }

    protected function makeBaseGlobalFromFile($handle, $path, $data)
    {
        return GlobalSet::make()
            ->id($data['id'])
            ->handle($handle)
            ->title($data['title'] ?? null)
            ->blueprint($data['blueprint'] ?? null)
            ->sites($data['sites'] ?? null)
            ->initialPath($path);
    }

    protected function makeVariables($set, $site)
    {
        $variables = $set->makeLocalization($site);

        // todo: cache the reading and parsing of the file
        if (! File::exists($path = $variables->path())) {
            return;
        }
        $contents = File::get($path);
        $data = YAML::parse($contents);

        $variables
            ->initialPath($path)
            ->data(Arr::except($data, 'origin'));

        if ($origin = Arr::get($data, 'origin')) {
            $variables->origin($origin);
        }

        return $variables;
    }

    public function save($global)
    {
        // todo
    }
}
