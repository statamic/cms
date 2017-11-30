<?php

namespace Statamic\Stache\Drivers;

use Statamic\API\YAML;
use Statamic\API\Taxonomy;
use Statamic\Stache\Repository;

class TaxonomiesDriver extends AbstractDriver
{
    protected $relatable = false;
    protected $traverse_recursively = false;

    public function getFilesystemRoot()
    {
        return 'taxonomies';
    }

    public function createItem($path, $contents)
    {
        $folder = Taxonomy::create(pathinfo($path)['filename']);

        $folder->data(YAML::parse($contents));

        return $folder;
    }

    public function getItemId($item, $path)
    {
        return pathinfo($path)['filename'];
    }

    public function isMatchingFile($file)
    {
        return $file['type'] === 'file' && $file['extension'] === 'yaml';
    }

    public function toPersistentArray($repo)
    {
        return [
            'meta' => [
                'paths' => $repo->getPaths()->all()
            ]
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
        dd('taxonomy locale from path', $path);
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
        dd('taxonomy locale', $path, $data);
    }
}
