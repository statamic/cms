<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Structures\NavTree;
use Statamic\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class NavTreeStore extends BasicStore
{
    protected $defaultIndexes = ['path'];

    public function key()
    {
        return 'nav-trees';
    }

    public function getItemFilter(SplFileInfo $file)
    {
        return $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        return $this
            ->newTreeClassByPath($path)
            ->tree(YAML::file($path)->parse($contents)['tree'] ?? [])
            ->syncOriginal();
    }

    protected function newTreeClassByPath($path)
    {
        [$site, $handle] = $this->parseTreePath($path);

        return (new NavTree)
            ->initialPath($path)
            ->locale($site)
            ->handle($handle);
    }

    protected function parseTreePath($path)
    {
        $path = Str::after($path, $this->directory);
        $path = Str::before($path, '.yaml');

        if (substr_count($path, '/') === 0) {
            return [Site::default()->handle(), $path];
        }

        return explode('/', $path);
    }

    public function getItemKey($item)
    {
        return "{$item->handle()}::{$item->locale()}";
    }
}
