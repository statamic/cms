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

    public function move($user, $assetFolder)
    {
        $user = User::fromUser($user);

        if (! $user->hasPermission("move {$assetFolder->container()->handle()} assets")) {
            return false;
        }

        return $assetFolder
            ->assets(true)
            ->reject(fn ($asset) => $user->can('move', $asset))
            ->isEmpty();
    }

    public function rename($user, $assetFolder)
    {
        $user = User::fromUser($user);

        if (! $user->hasPermission("rename {$assetFolder->container()->handle()} assets")) {
            return false;
        }

        return $assetFolder
            ->assets(true)
            ->reject(fn ($asset) => $user->can('rename', $asset))
            ->isEmpty();
    }

    public function delete($user, $assetFolder)
    {
        $user = User::fromUser($user);

        if (! $user->hasPermission("delete {$assetFolder->container()->handle()} assets")) {
            return false;
        }

        return $assetFolder
            ->assets(true)
            ->reject(fn ($asset) => $user->can('delete', $asset))
            ->isEmpty();
    }
}
