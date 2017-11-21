<?php

namespace Statamic\Stache\Drivers;

use Illuminate\Support\Collection;
use Statamic\API\YAML;
use Statamic\API\Folder;

class UserGroupsDriver extends AbstractDriver
{
    public function getFilesystemDriver()
    {
        return Folder::disk()->filesystem()->getDriver();
    }

    public function getFilesystemRoot()
    {
        return 'site/settings/users';
    }

    /**
     * Get a collection of items based on any modified files
     *
     * @param Collection $modified  The modified files and their raw contents.
     * @return Collection           A collection of arrays with item (the object), and the file path.
     */
    public function getItems(Collection $modified)
    {
        // We know it's only ever going to be one modified file, and it's groups.yaml
        $file = $modified->first();
        $path = 'site/settings/users/groups.yaml';

        return collect(YAML::parse($file))->map(function ($data, $id) {
            $factory = app('Statamic\Contracts\Permissions\UserGroupFactory');
            return $factory->create($data, $id);
        })->map(function ($group) use ($path) {
            return ['item' => $group, 'path' => $path];
        });
    }

    public function isMatchingFile($file)
    {
        return $file['basename'] === 'groups.yaml';
    }

    public function toPersistentArray($repo)
    {
        return [
            'meta' => [
                'paths' => $repo->getPaths()
            ],
            'items' => ['data' => $repo->getItems()]
        ];
    }

    public function createItem($path, $contents)
    {
        // TODO: Implement createItem() method.
    }

    /**
     * Get the locale based on the path
     *
     * @param string $path
     * @return string
     */
    public function getLocaleFromPath($path)
    {
        dd('usergroups locale from path', $path);
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
        dd('usergroups locale', $path, $data);
    }

    /**
     * Make sure duplicate IDs are detected
     *
     * @param $item
     * @return array [unique bool, path, existing path, existing repo key, item id]
     */
    public function ensureUniqueId($item)
    {
        return; // @todo
    }
}
