<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class AssetFolderPolicy
{
    public function create($user, $assetContainer)
    {
        $user = User::fromUser($user);

        if (! $user->hasPermission("upload {$assetContainer->handle()} assets")) {
            return false;
        }

        return $assetContainer->createFolders();
    }

    public function delete($user, $assetFolder)
    {
        $user = User::fromUser($user);

        if (! $user->hasPermission("delete {$assetFolder->container()->handle()} assets")) {
            return false;
        }

        return $assetFolder->assets()->reject(function ($asset) use ($user) {
            return $user->can('delete', $asset);
        })->isEmpty();
    }
}
