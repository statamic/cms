<?php

namespace Statamic\Policies;

class AssetPolicy
{
    public function store($user, $asset)
    {
        return $user->hasPermission("upload {$asset->container()->handle()} assets");
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
