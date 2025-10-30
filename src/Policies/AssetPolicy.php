<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class AssetPolicy
{
    public function before($user)
    {
        $user = User::fromUser($user);

        if ($user->isSuper() || $user->hasPermission('configure asset containers')) {
            return true;
        }
    }

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
        return User::fromUser($user)->hasPermission("upload {$assetContainer->handle()} assets");
    }

    public function move($user, $asset)
    {
        return User::fromUser($user)->hasPermission("move {$asset->container()->handle()} assets");
    }

    public function rename($user, $asset)
    {
        return User::fromUser($user)->hasPermission("rename {$asset->container()->handle()} assets");
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

    public function reupload($user, $asset)
    {
        return $this->edit($user, $asset)
            && $this->store($user, $asset->container());
    }
}
