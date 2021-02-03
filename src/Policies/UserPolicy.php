<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class UserPolicy
{
    public function index($authed)
    {
        $authed = User::fromUser($authed);

        return $authed->hasPermission('view users');
    }

    public function view($authed, $user)
    {
        return $this->index($authed);
    }

    public function edit($authed, $user)
    {
        $user = User::fromUser($user);
        $authed = User::fromUser($authed);

        if ($authed->id() === $user->id()) {
            return true; // Users may edit their own profiles.
        }

        return $authed->hasPermission('edit users');
    }

    public function create($authed)
    {
        $authed = User::fromUser($authed);

        return $authed->hasPermission('create users');
    }

    public function delete($authed, $user)
    {
        $authed = User::fromUser($authed);

        return $authed->hasPermission('delete users');
    }

    public function editPassword($authed, $user)
    {
        $user = User::fromUser($user);
        $authed = User::fromUser($authed);

        if ($authed->id() === $user->id()) {
            return true; // Users may change their own passwords.
        }

        return $authed->hasPermission('change passwords');
    }

    public function sendPasswordReset($authed, $user)
    {
        return $this->edit($authed, $user);
    }
}
