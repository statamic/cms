<?php

namespace Statamic\Stache\Drivers;

use Statamic\API\PageFolder;
use Statamic\API\Path;
use Statamic\API\Str;
use Statamic\API\YAML;
use Statamic\Stache\Repository;

class PageFoldersDriver extends AbstractDriver
{
    protected $relatable = false;

    public function getFilesystemRoot()
    {
        return 'pages';
    }

    public function createItem($path, $contents)
    {
        $folder = PageFolder::create();

        $folder->path($path);

        $folder->data(YAML::parse($contents));

        return $folder;
    }

    public function getItemId($item, $path)
    {
        // remove `pages` and `/folder.yaml` to turn it into a url
        return Str::ensureLeft(substr(Path::clean($path), 5, -12), '/');
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
        dd('page folder locale from path', $path);
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
        dd('page folder locale', $path, $data);
    }
}
