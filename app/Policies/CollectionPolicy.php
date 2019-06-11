<?php

namespace Statamic\Policies;

use Statamic\API\Collection;

class CollectionPolicy
{
    public function before($user, $ability)
    {
        if ($user->hasPermission('configure collections')) {
            return true;
        }
    }

    public function index($user)
    {
        if ($this->create($user)) {
            return true;
        }

        return ! Collection::all()->filter(function ($collection) use ($user) {
            return $this->view($user, $collection);
        })->isEmpty();
    }

    public function create($user)
    {
        return $user->hasPermission('configure collections');
    }

    public function store($user)
    {
        return $this->create($user);
    }

    public function view($user, $collection)
    {
        return $user->hasPermission("view {$collection->handle()} entries");
    }

    public function edit($user, $collection)
    {
        return $user->hasPermission('configure collections');
    }

    public function update($user, $collection)
    {
        return $this->edit($user, $collection);
    }

    public function delete($user, $collection)
    {
        return $user->hasPermission('configure collections');
    }

    public function reorder($user, $collection)
    {
        return $collection->orderable() && $user->hasPermission("reorder {$collection->handle()} entries");
    }
}
