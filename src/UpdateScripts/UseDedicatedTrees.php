<?php

namespace Statamic\UpdateScripts;

use Statamic\Facades\Collection;
use Statamic\Facades\File;
use Statamic\Facades\Nav;
use Statamic\Facades\Site;
use Statamic\Facades\Stache;
use Statamic\Facades\YAML;

class UseDedicatedTrees extends UpdateScript
{
    public function shouldUpdate($newVersion, $oldVersion)
    {
        return $this->isUpdatingTo('3.1.0-alpha.4');
    }

    public function update()
    {
        $this->updateCollections();
        $this->updateNavs();
    }

    private function updateCollections()
    {
        Collection::whereStructured()->each(function ($collection) {
            $yaml = YAML::file($collection->path())->parse();
            $tree = $yaml['structure']['tree'] ?? null;

            if (! $tree) {
                return; // Already migrated.
            }

            $trees = Site::hasMultiple() ? $tree : [Site::default()->handle() => $tree];

            foreach ($trees as $site => $tree) {
                $collection->structure()->makeTree($site, $tree)->save();
            }

            $collection->save();
        });
    }

    private function updateNavs()
    {
        Nav::all()->each(function ($nav) {
            $this->navTrees($nav)->each->save();
            $nav->save();
        });

        $this->deleteOldNavFiles();
    }

    private function navTrees($nav)
    {
        return Site::hasMultiple()
            ? $this->multiSiteNavTrees($nav)
            : $this->singleSiteNavTrees($nav);
    }

    private function multiSiteNavTrees($nav)
    {
        $dir = dirname($nav->path());

        return Site::all()->map(function ($site) use ($dir, $nav) {
            $path = $dir.'/'.$site->handle().'/'.$nav->handle().'.yaml';

            if (! File::exists($path)) {
                return null;
            }

            $yaml = YAML::file($path)->parse();

            return $nav->makeTree($site->handle(), $yaml['tree'] ?? []);
        })->filter();
    }

    private function singleSiteNavTrees($nav)
    {
        $yaml = YAML::file($nav->path())->parse();
        $tree = $yaml['tree'] ?? null;

        if (! $tree) {
            return collect(); // Already migrated
        }

        return collect([
            $nav->makeTree(Site::default()->handle(), $tree),
        ]);
    }

    private function deleteOldNavFiles()
    {
        $dir = Stache::store('navigation')->directory();

        foreach (Site::all() as $site) {
            File::delete($dir.'/'.$site->handle());
        }
    }
}
