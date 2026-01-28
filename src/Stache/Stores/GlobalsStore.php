<?php

namespace Statamic\Stache\Stores;

use Statamic\Facades\GlobalSet;
use Statamic\Facades\Path;
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
        $filename = Str::after(Path::tidy($file->getPathName()), $this->directory);

        return substr_count($filename, '/') === 0 && $file->getExtension() === 'yaml';
    }

    public function makeItemFromFile($path, $contents)
    {
        $relative = Str::after($path, $this->directory);
        $handle = Str::before($relative, '.yaml');

        $data = YAML::file($path)->parse();

        return $this->makeBaseGlobalFromFile($handle, $path, Arr::except($data, 'data'));
    }

    protected function makeBaseGlobalFromFile($handle, $path, $data)
    {
        return GlobalSet::make()
            ->handle($handle)
            ->title($data['title'] ?? null)
            ->sites($data['sites'] ?? [])
            ->initialPath($path);
    }
}
