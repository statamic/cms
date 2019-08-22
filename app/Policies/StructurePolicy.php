<?php

namespace Statamic\Policies;

use Statamic\API\Structure;

class StructurePolicy
{
    public function before($user, $ability)
    {
        $user = $user->statamicUser();

        if ($user->hasPermission('configure structures')) {
            return true;
        }
    }

    public function index($user)
    {
        $user = $user->statamicUser();

        if ($this->create($user)) {
            return true;
        }

        return ! Structure::all()->filter(function ($structure) use ($user) {
            return $this->view($user, $structure);
        })->isEmpty();
    }

    public function create($user)
    {
        $user = $user->statamicUser();

        return $user->hasPermission("configure structures");
    }

    public function store($user)
    {
        $user = $user->statamicUser();

        return $this->create($user);
    }

    public function view($user, $structure)
    {
        $user = $user->statamicUser();

        return $user->hasPermission("view {$structure->handle()} structure");
    }

    public function edit($user, $structure)
    {
        $user = $user->statamicUser();

        return $user->hasPermission("edit {$structure->handle()} structure");
    }

    public function update($user, $structure)
    {
        $user = $user->statamicUser();

        return $this->edit($user, $structure);
    }

    public function delete($user, $structure)
    {
        $user = $user->statamicUser();

        return $user->hasPermission("configure structures");
    }
}
