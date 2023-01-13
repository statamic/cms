<?php

namespace Statamic\Policies;

use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;

class AssetContainerPolicy
{
    public function before($user, $ability)
    {
        $user = User::fromUser($user);

        if ($user->hasPermission('configure asset containers')) {
            return true;
        }
    }

    public function index($user)
    {
        return AssetContainer::all()->filter(function ($container) use ($user) {
            return $this->view($user, $container);
        })->isNotEmpty();
    }

    public function create($user)
    {
        // handled by before()
    }

    public function view($user, $container)
    {
        $user = User::fromUser($user);

        $user->hasPermission("view {$container->handle()} assets");
    }

    public function edit($user, $container)
    {
        // handled by before()
    }

    public function update($user, $container)
    {
        // handled by before()
    }

    public function delete($user, $container)
    {
        // handled by before()
    }
}
