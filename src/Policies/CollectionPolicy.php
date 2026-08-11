<?php

namespace Statamic\Policies;

use Statamic\Facades\Collection;
use Statamic\Facades\Site as Sites;
use Statamic\Facades\User;
use Statamic\Sites\Site;

class CollectionPolicy
{
    use Concerns\HasMultisitePolicy;

    public function before($user)
    {
        $user = User::fromUser($user);

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

        return ! Collection::all()->filter(function ($collection) use ($user) {
            return $this->view($user, $collection);
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

    public function view($user, $collection)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("view {$collection->handle()} entries")
            && $this->userCanAccessAnySite($user, $collection->sites());
    }

    // `view` only requires access to *any* of the collection's sites, which is the right
    // question for "should this appear in the CP at all". When a request names a site,
    // that site is the one that needs checking.
    public function viewInSite($user, $collection, $site)
    {
        return $this->view($user, $collection)
            && $this->userCanAccessGivenSite($user, $site);
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

    public function reorderInSite($user, $collection, $site)
    {
        return $this->reorder($user, $collection)
            && $this->userCanAccessGivenSite($user, $site);
    }

    private function userCanAccessGivenSite($user, $site)
    {
        if (! $site instanceof Site) {
            $site = Sites::get($site);
        }

        return $site ? $this->userCanAccessSite(User::fromUser($user), $site) : false;
    }
}
