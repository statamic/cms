<?php

namespace Statamic\Stache\Drivers;

use Statamic\API\AssetContainer;
use Statamic\API\URL;
use Statamic\API\File;
use Statamic\API\Folder;
use Statamic\API\YAML;

class AssetContainersDriver extends AbstractDriver
{
    protected $relatable = false;

    public function getFilesystemRoot()
    {
        return 'assets';
    }

    public function createItem($path, $contents)
    {
        $data = YAML::parse($contents);

        // The path would be `assets/id.yaml` so we'll remove the directory and extension to get the ID.
        $id = substr($path, 7, -5);

        $disk = array_get($data, 'disk');
        $driver = config("filesystems.disks.$disk.driver");

        $container = AssetContainer::create();
        $container->id($id);
        $container->driver($driver);
        $container->path(array_get($data, 'path'));
        $container->data(YAML::parse($contents));
        $container->url($this->getUrl($id, $driver, $data));

        return $container;
    }

    private function getUrl($id, $driver, $data)
    {
        return File::disk($data['disk'])->url('/');
    }

    public function isMatchingFile($file)
    {
        return $file['extension'] === 'yaml';
    }

    public function toPersistentArray($repo)
    {
        return [
            'meta' => [
                'paths' => $repo->getPaths()->all(),
                'uris' => $repo->getUris()->all(),
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
        dd('asset container locale from path', $path);
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
        dd('asset container locale', $path, $data);
    }
}
