<?php

namespace Statamic\Policies;

class AssetPolicy
{
    public function store($user, $assetContainer)
    {
        if (! $user->hasPermission("upload {$assetContainer->handle()} assets")) {
            return false;
        }

        return $assetContainer->allowUploads();
    }

    public function move($user, $asset)
    {
        if (! $user->hasPermission("move {$asset->container()->handle()} assets")) {
            return false;
        }

        return $asset->container()->allowMoving();
    }

    public function rename($user, $asset)
    {
        if (! $user->hasPermission("rename {$asset->container()->handle()} assets")) {
            return false;
        }

        return $asset->container()->allowRenaming();
    }

    public function delete($user, $asset)
    {
        return $user->hasPermission("delete {$asset->container()->handle()} assets");
    }
}
