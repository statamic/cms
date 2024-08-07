<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades;
use Statamic\Facades\Path;
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
        $filename = Str::after(Path::tidy($file->getPathName()), $this->directory);

        return substr_count($filename, '/') === 0 && $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $relative = Str::after($path, $this->directory);
        $handle = Str::before($relative, '.yaml');

        $data = YAML::file($path)->parse($contents);

        return Facades\Nav::make()
            ->handle($handle)
            ->title($data['title'] ?? null)
            ->maxDepth($data['max_depth'] ?? null)
            ->collections($data['collections'] ?? null)
            ->expectsRoot($data['root'] ?? false)
            ->canSelectAcrossSites($data['select_across_sites'] ?? false)
            ->initialPath($path);
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
}
