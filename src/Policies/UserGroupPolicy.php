<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class UserGroupPolicy
{
    public function before($user)
    {
        if (User::fromUser($user)->isSuper()) {
            return true;
        }

        return $user->hasPermission('edit user groups');
    }

    public function index($user)
    {
        //
    }

    public function view($user, $group)
    {
        //
    }

    public function edit($user, $group)
    {
        //
    }

    public function create($user)
    {
        //
    }

    public function delete($user, $group)
    {
        //
    }
}
