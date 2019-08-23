<?php

namespace Statamic\Policies;

use Statamic\API\User;
use Statamic\API\Collection;

class CollectionPolicy
{
    public function before($user, $ability)
    {
        $user = User::fromUser($user);

        if ($user->hasPermission('configure collections')) {
            return true;
        }
    }

    public function index($user)
    {
        $user = User::fromUser($user);

        if ($this->create($user)) {
            return true;
        }

        return ! Collection::all()->filter(function ($collection) use ($user) {
            return $this->view($user, $collection);
        })->isEmpty();
    }

    public function create($user)
    {
        $user = User::fromUser($user);

        return $user->hasPermission('configure collections');
    }

    public function store($user)
    {
        return $this->create($user);
    }

    public function view($user, $collection)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("view {$collection->handle()} entries");
    }

    public function edit($user, $collection)
    {
        $user = User::fromUser($user);

        return $user->hasPermission('configure collections');
    }

    public function update($user, $collection)
    {
        return $this->edit($user, $collection);
    }

    public function delete($user, $collection)
    {
        $user = User::fromUser($user);

        return $user->hasPermission('configure collections');
    }

    public function reorder($user, $collection)
    {
        $user = User::fromUser($user);

        return $collection->orderable() && $user->hasPermission("reorder {$collection->handle()} entries");
    }
}
