<?php

namespace Statamic\Policies;

use Illuminate\Support\Facades\Gate;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Facades\User;

class AssetFolderPolicy
{
    public function create($user, $assetContainer)
    {
        $user = User::fromUser($user);

        if ($user->isSuper()) {
            return true;
        }

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

        if ($this->isUsingCustomAssetPolicy()) {
            return $assetFolder
                ->assets(true)
                ->reject(fn ($asset) => $user->can('move', $asset))
                ->isEmpty();
        }

        return $assetFolder->container()->allowMoving();
    }

    public function rename($user, $assetFolder)
    {
        $user = User::fromUser($user);

        if (! $user->hasPermission("rename {$assetFolder->container()->handle()} assets")) {
            return false;
        }

        if ($this->isUsingCustomAssetPolicy()) {
            return $assetFolder
                ->assets(true)
                ->reject(fn ($asset) => $user->can('rename', $asset))
                ->isEmpty();
        }

        return $assetFolder->container()->allowRenaming();
    }

    public function delete($user, $assetFolder)
    {
        $user = User::fromUser($user);

        if (! $user->hasPermission("delete {$assetFolder->container()->handle()} assets")) {
            return false;
        }

        if ($this->isUsingCustomAssetPolicy()) {
            return $assetFolder
                ->assets(true)
                ->reject(fn ($asset) => $user->can('delete', $asset))
                ->isEmpty();
        }

        return true;
    }

    protected function isUsingCustomAssetPolicy()
    {
        return Gate::policies()[AssetContract::class] !== AssetPolicy::class;
    }
}
