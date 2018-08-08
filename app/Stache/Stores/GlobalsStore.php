<?php

namespace Statamic\Stache\Stores;

use Statamic\API\YAML;
use Statamic\API\GlobalSet;
use Statamic\API\Collection;

class GlobalsStore extends BasicStore
{
    public function key()
    {
        return 'globals';
    }

    public function createItemFromFile($path, $contents)
    {
        $handle = pathinfo($path)['filename'];

        return GlobalSet::create($handle)
            ->with(YAML::parse($contents))
            ->get();
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
        return $this->paths->map(function ($path) {
            return pathinfo($path, PATHINFO_FILENAME);
        })->flip()->get($handle);
    }
}
