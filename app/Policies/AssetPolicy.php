<?php

namespace Statamic\Policies;

class AssetPolicy
{
    public function store($user, $container)
    {
        return $user->hasPermission("upload {$container->handle()} assets");
    }
}
