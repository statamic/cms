<?php

namespace Statamic\Policies;

use Statamic\API\Structure;

class StructurePolicy
{
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
        return $this->canConfigure($user);
    }

    public function view($user, $structure)
    {
        return $this->canConfigure($user)
            || $user->hasPermission("view {$structure->handle()} structure");
    }

    protected function canConfigure($user)
    {
        return $user->hasPermission('configure structures');
    }
}
