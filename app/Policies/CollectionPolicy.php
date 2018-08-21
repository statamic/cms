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
        //
    }

    public function view($user, $collection)
    {
        return $user->hasPermission("view {$collection->path()} collection");
    }

    public function edit($user, $collection)
    {
        return $user->hasPermission("edit {$collection->path()} collection");
    }

    public function delete($user, $collection)
    {
        //
    }
}
