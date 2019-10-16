<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class TermPolicy
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

    public function create($user, $taxonomy)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("create {$taxonomy->handle()} terms");
    }

    public function store($user, $taxonomy)
    {
        return $this->create($user, $taxonomy);
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
