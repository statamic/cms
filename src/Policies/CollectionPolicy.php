<?php

namespace Statamic\Policies;

use Statamic\Facades\Collection;
use Statamic\Facades\User;
use Statamic\Sites\Site;

class CollectionPolicy
{
    use Concerns\HasMultisitePolicy;

    public function before($user)
    {
        $user = User::fromUser($user);

        if ($user->isSuper() || $user->hasPermission('configure collections')) {
            return true;
        }
    }

    public function index($user, ?Site $site = null)
    {
        $user = User::fromUser($user);

        if ($this->create($user)) {
            return true;
        }

        return Collection::all()
            ->filter(fn ($collection) => $this->view($user, $collection))
            ->filter(fn ($collection) => ! $site || $collection->sites()->contains($site->handle()))
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

        return $user->hasPermission("view {$collection->handle()} entries")
            && $this->userCanAccessAnySite($user, $collection->sites());
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
