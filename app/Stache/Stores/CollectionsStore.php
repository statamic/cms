<?php

namespace Statamic\Stache\Stores;

use Statamic\API\YAML;
use Statamic\API\Collection;
use Statamic\Contracts\Data\Entries\Collection as CollectionContract;

class CollectionsStore extends BasicStore
{
    public function key()
    {
        return 'collections';
    }

    public function createItemFromFile($path, $contents)
    {
        $handle = pathinfo($path, PATHINFO_FILENAME);
        $data = YAML::parse($contents);

        return Collection::create($handle)
            ->title(array_get($data, 'title'))
            ->route(array_get($data, 'route'))
            ->order(array_get($data, 'order'))
            ->sites(array_get($data, 'sites'))
            ->template(array_get($data, 'template'))
            ->layout(array_get($data, 'layout'))
            ->data(array_get($data, 'data'))
            ->entryBlueprints(array_get($data, 'blueprints'));
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

    public function save(CollectionContract $collection)
    {
        $path = $this->directory . '/' . $collection->handle() . '.yaml';
        $contents = YAML::dump($collection->data());

        $this->files->put($path, $contents);
    }
}
