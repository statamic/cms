<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class AssetPolicy
{
    public function view($user, $asset)
    {
        return User::fromUser($user)->hasPermission("view {$asset->containerHandle()} assets");
    }

    public function edit($user, $asset)
    {
        return User::fromUser($user)->hasPermission("edit {$asset->containerHandle()} assets");
    }

    public function store($user, $assetContainer)
    {
        $user = User::fromUser($user);

        if (! $user->hasPermission("upload {$assetContainer->handle()} assets")) {
            return false;
        }

        return $assetContainer->allowUploads();
    }

    public function move($user, $asset)
    {
        $user = User::fromUser($user);

        if (! $user->hasPermission("move {$asset->container()->handle()} assets")) {
            return false;
        }

        return $asset->container()->allowMoving();
    }

    public function rename($user, $asset)
    {
        $user = User::fromUser($user);

        if (! $user->hasPermission("rename {$asset->container()->handle()} assets")) {
            return false;
        }

        return $asset->container()->allowRenaming();
    }

    public function delete($user, $asset)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("delete {$asset->container()->handle()} assets");
    }

    public function replace($user, $asset)
    {
        return $this->edit($user, $asset)
            && $this->store($user, $asset->container())
            && $this->delete($user, $asset);
    }
}
