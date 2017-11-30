<?php

namespace Statamic\Stache\Drivers;

use Statamic\API\Collection;
use Statamic\API\YAML;

class CollectionsDriver extends AbstractDriver
{
    protected $relatable = false;

    public function getFilesystemRoot()
    {
        return 'collections';
    }

    public function createItem($path, $contents)
    {
        $collection = Collection::create(explode('/', $path)[1]);

        $collection->data(YAML::parse($contents));

        return $collection;
    }

    public function getItemId($item, $path)
    {
        return explode('/', $path)[1];
    }

    public function isMatchingFile($file)
    {
        return $file['basename'] === 'folder.yaml';
    }

    public function toPersistentArray($repo)
    {
        return [
            'meta' => [
                'paths' => $repo->getPaths()->all()
            ],
            'items' => ['data' => $repo->getItems()]
        ];
    }

    /**
     * Get the locale based on the path
     *
     * @param string $path
     * @return string
     */
    public function getLocaleFromPath($path)
    {
        dd('collection locale from path', $path);
    }

    /**
     * Get the localized URL
     *
     * @param        $locale
     * @param array  $data
     * @param string $path
     * @return string
     */
    public function getLocalizedUri($locale, $data, $path)
    {
        dd('collection locale', $path, $data);
    }
}
