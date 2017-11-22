<?php

namespace Statamic\Addons\Can;

use Statamic\API\User;
use Statamic\Extend\API;

class CanAPI extends API
{
    public function can($permissions)
    {
        // Not logged in? This is the end of the road.
        if (! $user = User::getCurrent()) {
            return false;
        }

        $permissions = explode('|', $permissions);

        foreach ($permissions as $permission) {
            if ($user->can($permission)) {
                return true;
            }
        }

        return false;
    }
}
