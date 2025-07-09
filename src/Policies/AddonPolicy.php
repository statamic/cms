<?php

namespace Statamic\Policies;

use Statamic\Facades\User;

class AddonPolicy
{
    public function before($user)
    {
        $user = User::fromUser($user);

        if ($user->isSuper() || $user->hasPermission('configure addons')) {
            return true;
        }
    }

    public function index($user)
    {
        //
    }

    public function editSettings($user, $addon)
    {
        $user = User::fromUser($user);

        return $user->hasPermission("edit {$addon->slug()} settings");
    }
}
