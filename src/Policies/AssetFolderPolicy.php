<?php

namespace Statamic\Policies;

use Illuminate\Support\Facades\Gate;
use Statamic\Contracts\Assets\Asset as AssetContract;
use Statamic\Facades\User;

class AssetFolderPolicy
{
    public function before($user)
    {
        $user = User::fromUser($user);

        if ($user->hasPermission('configure asset containers')) {
            return true;
        }
    }

    public function create($user, $assetContainer)
    {
        $user = User::fromUser($user);

        $permission = config('statamic.assets.v6_permissions')
            ? "edit {$assetContainer->handle()} folders"
            : "upload {$assetContainer->handle()} assets";

        if (! $user->hasPermission($permission)) {
            return false;
        }

        return $assetContainer->createFolders();
    }

    public function move($user, $assetFolder)
    {
        $user = User::fromUser($user);

        $hasPermission = config('statamic.assets.v6_permissions')
            ? ($user->hasPermission("edit {$assetFolder->container()->handle()} folders")
                && $user->hasPermission("move {$assetFolder->container()->handle()} assets"))
            : $user->hasPermission("move {$assetFolder->container()->handle()} assets");

        if (! $hasPermission) {
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

        $hasPermission = config('statamic.assets.v6_permissions')
            ? ($user->hasPermission("edit {$assetFolder->container()->handle()} folders")
                && $user->hasPermission("rename {$assetFolder->container()->handle()} assets"))
            : $user->hasPermission("rename {$assetFolder->container()->handle()} assets");

        if (! $hasPermission) {
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

        $hasPermission = config('statamic.assets.v6_permissions')
            ? ($user->hasPermission("edit {$assetFolder->container()->handle()} folders")
                && $user->hasPermission("delete {$assetFolder->container()->handle()} assets"))
            : $user->hasPermission("delete {$assetFolder->container()->handle()} assets");

        if (! $hasPermission) {
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
