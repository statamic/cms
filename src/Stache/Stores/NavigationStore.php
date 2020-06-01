<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades;
use Statamic\Facades\File;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class NavigationStore extends BasicStore
{
    public function key()
    {
        return 'navigation';
    }

    public function getItemFilter(SplFileInfo $file)
    {
        // The structures themselves should only exist in the root
        // (ie. no slashes in the filename)
        $filename = str_after(Path::tidy($file->getPathName()), $this->directory);

        return substr_count($filename, '/') === 0 && $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $relative = str_after($path, $this->directory);
        $handle = str_before($relative, '.yaml');

        // If it's a tree file that was requested, instead assume that the
        // base file was requested. The tree will get made as part of it.
        if (Site::hasMultiple() && Str::contains($relative, '/')) {
            [$site, $relative] = explode('/', $relative, 2);
            $handle = str_before($relative, '.yaml');
            $path = $this->directory.$handle.'.yaml';
            $data = YAML::file($path)->parse();

            return $this->makeMultiSiteStructureFromFile($handle, $path, $data);
        }

        $data = YAML::file($path)->parse($contents);

        return Site::hasMultiple()
            ? $this->makeMultiSiteStructureFromFile($handle, $path, $data)
            : $this->makeSingleSiteStructureFromFile($handle, $path, $data);
    }

    protected function makeSingleSiteStructureFromFile($handle, $path, $data)
    {
        $structure = $this
            ->makeBaseStructureFromFile($handle, $path, $data)
            ->maxDepth($data['max_depth'] ?? null)
            ->collections($data['collections'] ?? null);

        return $structure->addTree(
            $structure
                ->makeTree(Site::default()->handle())
                ->tree($data['tree'] ?? [])
        );
    }

    protected function makeMultiSiteStructureFromFile($handle, $path, $data)
    {
        $structure = $this->makeBaseStructureFromFile($handle, $path, $data);

        Site::all()->filter(function ($site) use ($handle) {
            return File::exists($this->directory.$site->handle().'/'.$handle.'.yaml');
        })->map->handle()->map(function ($site) use ($structure) {
            return $this->makeTree($structure, $site);
        })->filter()->each(function ($variables) use ($structure) {
            $structure->addTree($variables);
        });

        return $structure;
    }

    protected function makeBaseStructureFromFile($handle, $path, $data)
    {
        return Facades\Nav::make()
            ->handle($handle)
            ->title($data['title'] ?? null)
            ->maxDepth($data['max_depth'] ?? null)
            ->collections($data['collections'] ?? null)
            ->expectsRoot($data['root'] ?? false)
            ->initialPath($path);
    }

    protected function makeTree($structure, $site)
    {
        $tree = $structure->makeTree($site);

        // todo: cache the reading and parsing of the file
        if (! File::exists($path = $tree->path())) {
            return;
        }
        $data = YAML::file($path)->parse();

        return $tree
            ->initialPath($path)
            ->tree($data['tree'] ?? []);
    }

    public function getItemKey($item)
    {
        return $item->handle();
    }

    protected function getKeyFromPath($path)
    {
        if ($key = parent::getKeyFromPath($path)) {
            return $key;
        }

        return pathinfo($path, PATHINFO_FILENAME);
    }

    public function filter($file)
    {
        return $file->getExtension() === 'yaml';
    }

    public function save($nav)
    {
        parent::save($nav);

        if (Site::hasMultiple()) {
            Site::all()->each(function ($site) use ($nav) {
                $site = $site->handle();
                $nav->existsIn($site) ? $nav->in($site)->writeFile() : $nav->makeTree($site)->deleteFile();
            });
        }
    }
}
