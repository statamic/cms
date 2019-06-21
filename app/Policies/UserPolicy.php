<?php

namespace Statamic\Policies;

class UserPolicy
{
    public function index($authed)
    {
        return $authed->hasPermission('view users');
    }

    public function view($authed, $user)
    {
        return $this->index($authed);
    }

    public function edit($authed, $user)
    {
        if ($authed === $user) {
            return true; // Users may edit their own profiles.
        }

        return $authed->hasPermission('edit users');
    }

    public function create($authed)
    {
        return $authed->hasPermission('create users');
    }

    public function delete($authed, $user)
    {
        return $authed->hasPermission('delete users');
    }

    public function editPassword($authed, $user)
    {
        if ($authed === $user) {
            return true; // Users may change their own passwords.
        }

        return $authed->hasPermission('change passwords');
    }

    public function sendActivationEmail($authed, $user)
    {
        return $this->edit($authed, $user);
    }
}
