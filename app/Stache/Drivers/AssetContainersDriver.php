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

    public function getFilesystemDriver()
    {
        return Folder::disk('content')->filesystem()->getDriver();
    }

    public function getFilesystemRoot()
    {
        return 'assets';
    }

    public function createItem($path, $contents)
    {
        $data = YAML::parse($contents);

        // The path would be `assets/id.yaml` so we'll remove the directory and extension to get the ID.
        $id = substr($path, 7, -5);

        $driver = array_get($data, 'driver', 'local');

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
        $method = 'get'.ucfirst($driver).'Url';

        return $this->$method($id, $data);
    }

    private function getLocalUrl($id, $data)
    {
        return array_get($data, 'url');
    }

    private function getS3Url($id, $data)
    {
        // Double getAdapter since we're using CachedAdapter for s3.
        $adapter = File::disk("assets:$id")->filesystem()->getAdapter()->getAdapter();

        $url = rtrim($adapter->getClient()->getObjectUrl($adapter->getBucket(), array_get($data, 'path', '/')), '/');

        return URL::tidy($url);
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
