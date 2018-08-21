<?php

namespace Statamic\Policies;

use Statamic\API\Structure;

class StructurePolicy
{
    public function before($user, $ability)
    {
        if ($user->hasPermission('configure structures')) {
            return true;
        }
    }

    public function index($user)
    {
        if ($this->create($user)) {
            return true;
        }

        return ! Structure::all()->filter(function ($structure) use ($user) {
            return $this->view($user, $structure);
        })->isEmpty();
    }

    public function create($user)
    {
        //
    }

    public function view($user, $structure)
    {
        return $user->hasPermission("view {$structure->handle()} structure");
    }

    public function edit($user, $structure)
    {
        return $user->hasPermission("edit {$structure->handle()} structure");
    }

    public function delete($user, $structure)
    {
        //
    }
}
