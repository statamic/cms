<?php

namespace Statamic\Policies;

use Statamic\Contracts\Globals\GlobalSet;
use Statamic\Facades\GlobalSet as GlobalSetFacade;
use Statamic\Facades\Site;
use Statamic\Facades\User;

class GlobalSetPolicy
{
    public function before($user, $ability, $set)
    {
        $user = User::fromUser($user);
        $site = Site::selected();

        if ($user->hasPermission('configure globals') &&
            $user->hasPermission("access {$site->handle()} site") &&
            $set instanceof GlobalSet &&
            $set->existsIn($site->handle())) {
            return true;
        }
    }

    public function index($user)
    {
        $user = User::fromUser($user);

        if ($this->create($user)) {
            return true;
        }

        return ! GlobalSetFacade::all()->filter(function ($set) use ($user) {
            return $this->view($user, $set);
        })->isEmpty();
    }

    public function create($user)
    {
        // handled by before()
    }

    public function view($user, $set)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("edit {$set->handle()} globals");
    }

    public function edit($user, $set)
    {
        // handled by before()
    }

    public function configure($user, $set)
    {
        // handled by before()
    }

    public function delete($user, $set)
    {
        // handled by before()
    }
}
