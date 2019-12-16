<?php

namespace Statamic\Policies;

use Statamic\Facades\User;
use Statamic\Facades\Taxonomy;

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
        $user = User::fromUser($user);

        return $user->hasPermission('configure taxonomies');
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
        $user = User::fromUser($user);

        return $user->hasPermission('configure taxonomies');
    }

    public function update($user, $taxonomy)
    {
        return $this->edit($user, $taxonomy);
    }

    public function delete($user, $taxonomy)
    {
        $user = User::fromUser($user);

        return $user->hasPermission('configure taxonomies');
    }

    public function reorder($user, $taxonomy)
    {
        $user = User::fromUser($user);

        return $taxonomy->orderable() && $user->hasPermission("reorder {$taxonomy->handle()} terms");
    }
}
