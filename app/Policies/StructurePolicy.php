<?php

namespace Statamic\Policies;

use Statamic\API\Structure;

class StructurePolicy
{
    public function index($user)
    {
        return ! Structure::all()->filter(function ($structure) use ($user) {
            return $this->view($user, $structure);
        })->isEmpty();
    }

    public function view($user, $structure)
    {
        return $user->hasPermission("view {$structure->handle()} structure");
    }
}
