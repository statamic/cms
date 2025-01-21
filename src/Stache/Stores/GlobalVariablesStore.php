<?php

namespace Statamic\Stache\Stores;

use Statamic\Contracts\Globals\Variables;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Facades\YAML;
use Statamic\Support\Arr;
use Statamic\Support\Str;
use Symfony\Component\Finder\SplFileInfo;

class GlobalVariablesStore extends BasicStore
{
    public function key()
    {
        return 'global-variables';
    }

    public function getItemFilter(SplFileInfo $file)
    {
        if ($file->getExtension() !== 'yaml') {
            return false;
        }

        $filename = Str::after(Path::tidy($file->getPathName()), $this->directory);

        if (! Site::multiEnabled()) {
            return substr_count($filename, '/') === 0;
        }

        return substr_count($filename, '/') === 1;
    }

    public function makeItemFromFile($path, $contents)
    {
        $relative = Str::after($path, $this->directory);
        $handle = Str::before($relative, '.yaml');

        $data = YAML::file($path)->parse($contents);

        if (! Site::multiEnabled()) {
            $data = $data['data'] ?? [];
        }

        return $this->makeVariablesFromFile($handle, $path, $data);
    }

    protected function makeVariablesFromFile($handle, $path, $data)
    {
        $variables = app(Variables::class)
            ->initialPath($path)
            ->data(Arr::except($data, 'origin'));

        $handle = explode('/', $handle);
        if (count($handle) > 1) {
            $variables->globalSet($handle[1])
                ->locale($handle[0]);
        } else {
            $variables->globalSet($handle[0])
                ->locale(Site::default()->handle());
        }

        if ($origin = Arr::get($data, 'origin')) {
            $variables->origin($origin);
        }

        return $variables;
    }

    protected function writeItemToDisk($item)
    {
        if (Site::multiEnabled()) {
            $item->writeFile();
        } else {
            $item->globalSet()->writeFile();
        }
    }

    protected function deleteItemFromDisk($item)
    {
        if (Site::multiEnabled()) {
            $item->deleteFile();
        } else {
            $item->globalSet()->removeLocalization($item)->writeFile();
        }
    }

    protected function storeIndexes()
    {
        return [
            'handle',
        ];
    }
}
