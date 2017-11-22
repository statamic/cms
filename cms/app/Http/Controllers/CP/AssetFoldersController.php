<?php

namespace Statamic\Http\Controllers;

use Statamic\API\AssetContainer;
use Statamic\API\Folder;
use Statamic\API\Helper;
use Statamic\API\Path;
use Statamic\API\Stache;
use Statamic\Http\Requests\StoreAssetFolder;

class AssetFoldersController extends CpController
{
    /**
     * Create a new folder
     *
     * @return array
     */
    public function store(StoreAssetFolder $request)
    {
        $this->request = $request;

        $container = AssetContainer::find($this->request->input('container'));

        $path = ltrim(Path::assemble(
            $this->request->input('parent'),
            $this->request->input('basename')
        ), '/');

        $folder = $container->assetFolder($path);

        if ($this->request->has('title')) {
            $folder->set('title', $this->request->input('title'));
        }

        $folder->save();

        return ['success' => true, 'message' => 'Folder created', 'folder' => $folder->toArray()];
    }

    /**
     * Get the data for an existing folder in order to edit it
     *
     * @param string $container
     * @param string $folder
     * @return array
     */
    public function edit($container, $folder = '/')
    {
        $container = AssetContainer::find($container);

        $folder = $container->assetFolder($folder);

        return $folder->toArray();
    }

    /**
     * Update an existing folder
     *
     * @param string $container
     * @param string $folder
     * @return array
     */
    public function update($container, $folder = '/')
    {
        $container = AssetContainer::find($container);

        $folder = $container->assetFolder($folder);

        if ($this->request->has('title')) {
            $folder->set('title', $this->request->input('title'));
        } else {
            $folder->remove('title');
        }

        $folder->save();

        return ['success' => true, 'message' => 'Folder updated', 'folder' => $folder->toArray()];
    }

    /**
     * Delete one or more folder in a container
     *
     * @return array
     */
    public function delete()
    {
        $container = AssetContainer::find($this->request->input('container'));

        $folders = Helper::ensureArray($this->request->input('folders'));

        foreach ($folders as $folder) {
            $container->assetFolder($folder)->delete();
        }

        return ['success' => true];
    }
}
