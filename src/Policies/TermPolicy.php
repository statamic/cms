<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class TermPolicy
{
    use Concerns\HasMultisitePolicy;

    public function before($user)
    {
        $user = User::fromUser($user);

        if (
            $user->isSuper() ||
            $user->hasPermission('configure taxonomies')
        ) {
            return true;
        }
    }

    public function index($user)
    {
        //
    }

    public function view($user, $term)
    {
        $user = User::fromUser($user);

        return $this->edit($user, $term)
            || $user->hasPermission("view {$term->taxonomyHandle()} terms");
    }

    public function edit($user, $term)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("edit {$term->taxonomyHandle()} terms");
    }

    public function update($user, $term)
    {
        $user = User::fromUser($user);

        return $this->edit($user, $term);
    }

    public function create($user, $taxonomy, $site = null)
    {
        $user = User::fromUser($user);

        if ($site && (! $taxonomy->sites()->contains($site->handle()) || ! $this->userCanAccessSite($user, $site))) {
            return false;
        }

        return $user->hasPermission("create {$taxonomy->handle()} terms");
    }

    public function store($user, $taxonomy, $site = null)
    {
        return $this->create($user, $taxonomy, $site);
    }

    public function delete($user, $term)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("delete {$term->taxonomyHandle()} terms");
    }

    public function publish($user, $term)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("publish {$term->taxonomyHandle()} terms");
    }
}
