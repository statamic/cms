<?php

namespace Statamic\Policies;

use Statamic\Contracts\Entries\Collection;
use Statamic\Facades\Collection as CollectionFacade;
use Statamic\Facades\Site;
use Statamic\Facades\User;

class CollectionPolicy
{
    public function before($user, $ability, $collection)
    {
        $user = User::fromUser($user);
        $site = Site::selected();

        if (
            $collection instanceof Collection &&
            ! $collection->existsIn($site->handle())) {
            return false;
        }
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

        return ! CollectionFacade::all()->filter(function ($collection) use ($user) {
            return $this->view($user, $collection);
        })->isEmpty();
    }

    public function create($user)
    {
        $user = User::fromUser($user);
        $site = Site::selected();

        return  $user->hasPermission('configure collections') && $user->hasPermission("access {$site->handle()} site");
    }

    public function store($user)
    {
        $user = User::fromUser($user);
        $site = Site::selected();

        return  $user->hasPermission('configure collections') && $user->hasPermission("access {$site->handle()} site");
    }

    public function view($user, $collection)
    {
        $user = User::fromUser($user);
        $site = Site::selected();

        return ($user->hasPermission('configure collections') || $user->hasPermission("view {$collection->handle()} entries")) &&
               $user->hasPermission("access {$site->handle()} site") &&
               $collection->existsIn($site->handle());
    }

    public function edit($user, $collection)
    {
        // handled by before()
    }

    public function update($user, $collection)
    {
        // handled by before()
    }

    public function delete($user, $collection)
    {
        // handled by before()
    }

    public function reorder($user, $collection)
    {
        $user = User::fromUser($user);

        return $collection->hasStructure() && $user->hasPermission("reorder {$collection->handle()} entries");
    }
}
