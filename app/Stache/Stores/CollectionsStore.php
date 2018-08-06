<?php

namespace Statamic\Stache\Stores;

use Statamic\API\YAML;
use Statamic\API\Collection;

class CollectionsStore extends BasicStore
{
    public function key()
    {
        return 'collections';
    }

    public function createItemFromFile($path, $contents)
    {
        $id = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::parse($contents);

        $collection = Collection::create($id);
        $collection->data($data);
        return $collection;
    }

    public function getItemKey($item, $path)
    {
        return pathinfo($path)['filename'];
    }

    public function filter($file)
    {
        $relative = $file->getPathname();

        $dir = str_finish($this->directory, '/');

        if (substr($relative, 0, strlen($dir)) == $dir) {
            $relative = substr($relative, strlen($dir));
        }

        return $file->getExtension() === 'yaml' && substr_count($relative, '/') === 0;
    }
}
