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
        return $user->hasPermission("move {$asset->container()->handle()} assets");
    }

    public function delete($user, $asset)
    {
        return $user->hasPermission("delete {$asset->container()->handle()} assets");
    }
}
