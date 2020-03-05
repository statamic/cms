<?php

namespace Statamic\Policies;

use Statamic\Facades\User;
use Statamic\Facades\Structure;

class StructurePolicy
{
    public function before($user, $ability)
    {
        $user = User::fromUser($user);

        if ($user->hasPermission('configure structures')) {
            return true;
        }
    }

    public function index($user)
    {
        $user = User::fromUser($user);

        if ($this->create($user)) {
            return true;
        }

        return ! Structure::all()->filter(function ($structure) use ($user) {
            return $this->view($user, $structure);
        })->isEmpty();
    }

    public function create($user)
    {
        // handled by before()
    }

    public function store($user)
    {
        // handled by before()
    }

    public function view($user, $structure)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("view {$structure->handle()} structure");
    }

    public function edit($user, $structure)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("edit {$structure->handle()} structure");
    }

    public function update($user, $structure)
    {
        $user = User::fromUser($user);

        return $this->edit($user, $structure);
    }

    public function delete($user, $structure)
    {
        // handled by before()
    }
}
