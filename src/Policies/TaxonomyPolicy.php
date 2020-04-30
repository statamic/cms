<?php

namespace Statamic\Policies;

use Statamic\Facades\Taxonomy;
use Statamic\Facades\User;

class TaxonomyPolicy
{
    public function before($user, $ability)
    {
        $user = User::fromUser($user);

        if ($user->hasPermission('configure taxonomies')) {
            return true;
        }
    }

    public function index($user)
    {
        $user = User::fromUser($user);

        if ($this->create($user)) {
            return true;
        }

        return ! Taxonomy::all()->filter(function ($taxonomy) use ($user) {
            return $this->view($user, $taxonomy);
        })->isEmpty();
    }

    public function create($user)
    {
        // handled by before()
    }

    public function store($user)
    {
        return $this->create($user);
    }

    public function view($user, $taxonomy)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("view {$taxonomy->handle()} terms");
    }

    public function edit($user, $taxonomy)
    {
        // handled by before()
    }

    public function update($user, $taxonomy)
    {
        // handled by before()
    }

    public function delete($user, $taxonomy)
    {
        // handled by before()
    }

    public function reorder($user, $taxonomy)
    {
        $user = User::fromUser($user);

        return $taxonomy->orderable() && $user->hasPermission("reorder {$taxonomy->handle()} terms");
    }
}
