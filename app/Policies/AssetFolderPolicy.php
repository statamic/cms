<?php

namespace Statamic\Policies;

class AssetFolderPolicy
{
    public function create($user, $assetContainer)
    {
        if (! $user->hasPermission("upload {$assetContainer->handle()} assets")) {
            return false;
        }

        return $assetContainer->createFolders();
    }

    public function delete($user, $assetFolder)
    {
        if (! $user->hasPermission("delete {$assetFolder->container()->handle()} assets")) {
            return false;
        }

        return $assetFolder->assets()->reject(function ($asset) use ($user) {
            return $user->can('delete', $asset);
        })->isEmpty();
    }
}
