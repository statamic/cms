<?php

namespace Statamic\Stache\Stores;

use Statamic\API;
use Statamic\API\Str;
use Statamic\API\File;
use Statamic\API\Site;
use Statamic\API\YAML;
use Statamic\Stache\Indexes;
use Symfony\Component\Finder\SplFileInfo;

class StructuresStore extends BasicStore
{
    protected $storeIndexes = [
        'uri' => Indexes\StructureUris::class,
    ];

    public function key()
    {
        return 'structures';
    }

    public function getItemFilter(SplFileInfo $file)
    {
        // The structures themselves should only exist in the root
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
            ? $this->makeMultiSiteStructureFromFile($handle, $path, $data)
            : $this->makeSingleSiteStructureFromFile($handle, $path, $data);
    }

    protected function makeSingleSiteStructureFromFile($handle, $path, $data)
    {
        $structure = $this
            ->makeBaseStructureFromFile($handle, $path, $data)
            ->sites([$site = Site::default()->handle()])
            ->maxDepth($data['max_depth'] ?? null)
            ->collections($data['collections'] ?? null);

        return $structure->addTree(
            $structure
                ->makeTree($site)
                ->root($data['root'] ?? null)
                ->tree($data['tree'] ?? [])
        );
    }

    protected function makeMultiSiteStructureFromFile($handle, $path, $data)
    {
        $structure = $this->makeBaseStructureFromFile($handle, $path, $data);

        $structure->sites()->map(function ($site) use ($structure) {
            return $this->makeTree($structure, $site);
        })->filter()->each(function ($variables) use ($structure) {
            $structure->addTree($variables);
        });

        return $structure;
    }

    protected function makeBaseStructureFromFile($handle, $path, $data)
    {
        return API\Structure::make()
            ->handle($handle)
            ->title($data['title'] ?? null)
            ->sites($data['sites'] ?? null)
            ->maxDepth($data['max_depth'] ?? null)
            ->collections($data['collections'] ?? null)
            ->expectsRoot($data['expects_root'] ?? false)
            ->initialPath($path);
    }

    protected function makeTree($structure, $site)
    {
        $tree = $structure->makeTree($site);

        // todo: cache the reading and parsing of the file
        if (! File::exists($path = $tree->path())) {
            return;
        }
        $contents = File::get($path);
        $data = YAML::parse($contents);

        return $tree
            ->initialPath($path)
            ->root($data['root'] ?? null)
            ->tree($data['tree'] ?? []);
    }

    public function getItemKey($item)
    {
        return $item->handle();
    }

    public function filter($file)
    {
        return $file->getExtension() === 'yaml';
    }

    public function save($structure)
    {
        // todo
    }

    public function delete($structure)
    {
        // todo
    }
}
