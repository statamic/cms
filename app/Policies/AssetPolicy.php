<?php

namespace Statamic\Policies;

class AssetPolicy
{
    public function store($user, $assetContainer)
    {
        $user = $user->statamicUser();

        if (! $user->hasPermission("upload {$assetContainer->handle()} assets")) {
            return false;
        }

        return $assetContainer->allowUploads();
    }

    public function move($user, $asset)
    {
        $user = $user->statamicUser();

        if (! $user->hasPermission("move {$asset->container()->handle()} assets")) {
            return false;
        }

        return $asset->container()->allowMoving();
    }

    public function rename($user, $asset)
    {
        $user = $user->statamicUser();

        if (! $user->hasPermission("rename {$asset->container()->handle()} assets")) {
            return false;
        }

        return $asset->container()->allowRenaming();
    }

    public function delete($user, $asset)
    {
        $user = $user->statamicUser();

        return $user->hasPermission("delete {$asset->container()->handle()} assets");
    }
}
