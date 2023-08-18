<?php

namespace Statamic\Policies;

use Statamic\Facades\Collection;
use Statamic\Facades\User;

class CollectionPolicy
{
    use Concerns\HasMultisitePolicy;

    public function before($user, $ability, $collection)
    {
        $user = User::fromUser($user);

        if ($user->hasPermission('configure collections')) {
            return true;
        }

        if ($this->siteIsForbidden($user, $collection)) {
            return false;
        }
    }

    public function index($user)
    {
        $user = User::fromUser($user);

        if ($this->create($user)) {
            return true;
        }

        return Collection::all()
            ->filter(fn ($collection) => $this->view($user, $collection))
            ->isNotEmpty();
    }

    public function create($user)
    {
        // handled by before()
    }

    public function store($user)
    {
        // handled by before()
    }

    public function view($user, $collection)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("view {$collection->handle()} entries");
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
