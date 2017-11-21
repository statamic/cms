<?php

namespace Statamic\Stache\Drivers;

use Statamic\API\Str;
use Statamic\API\URL;
use Statamic\API\Path;
use Statamic\Data\Pages\PageStructure;

class PageStructureDriver extends AbstractDriver
{
    protected $relatable = false;

    public function getFilesystemRoot()
    {
        return 'pages';
    }

    public function createItem($path, $contents)
    {
        $repo = $this->stache->repo('pages');

        $page = $repo->getItem($repo->getIdByPath($path));

        if (! $page) {
            return;
        }

        return $page->structure();
    }

    public function isMatchingFile($file)
    {
        return $file['filename'] === 'index';
    }

    /**
     * Get the locale based on the path
     *
     * @param string $path
     * @return string
     */
    public function getLocaleFromPath($path)
    {
        //
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
        //
    }

    public function toPersistentArray($repo)
    {
        $structures = $repo->getItems()->map(function ($structure) {
            return $structure->toArray();
        })->all();

        return [
            'meta' => [
                'paths' => $repo->getPaths()->all(),
                'uris' => $repo->getUris()->all(),
            ],
            'items' => ['data' => $structures]
        ];
    }

    public function load($collection)
    {
        return $collection->map(function ($item, $id) {
            $structure = new PageStructure($id);
            $structure->data($item);
            return $structure;
        });
    }
}
