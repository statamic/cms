<?php

namespace Statamic\Policies;

use Statamic\Facades\User;
use Statamic\Facades\GlobalSet;

class GlobalSetPolicy
{
    public function before($user, $ability)
    {
        $user = User::fromUser($user);

        if ($user->hasPermission('configure globals')) {
            return true;
        }
    }

    public function index($user)
    {
        $user = User::fromUser($user);

        if ($this->create($user)) {
            return true;
        }

        return ! GlobalSet::all()->filter(function ($set) use ($user) {
            return $this->view($user, $set);
        })->isEmpty();
    }

    public function view($user, $set)
    {
        $user = User::fromUser($user);

        return $this->edit($user, $set);
    }

    public function edit($user, $set)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("edit {$set->handle()} globals");
    }

    public function create($user)
    {
        //
    }

    public function delete($user, $set)
    {
        //
    }
}
