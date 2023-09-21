<?php

namespace Statamic\Policies;

use Statamic\Facades\AssetContainer;
use Statamic\Facades\User;

class AssetContainerPolicy
{
    public function before($user, $ability)
    {
        if (User::fromUser($user)->hasPermission('configure asset containers')) {
            return true;
        }
    }

    public function index($user)
    {
        $user = User::fromUser($user);

        return ! AssetContainer::all()->filter(function ($container) use ($user) {
            return $this->view($user, $container);
        })->isEmpty();
    }

    public function create($user)
    {
        // handled by before()
    }

    public function view($user, $container)
    {
        return User::fromUser($user)->can("view {$container->handle()} assets");
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
