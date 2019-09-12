<?php

namespace Statamic\Addons\Is;

use Statamic\Facades\Role;
use Statamic\Facades\User;
use Statamic\Extend\API;

class IsAPI extends API
{
    public function is($roles)
    {
        // Not logged in? This is the end of the road.
        if (! $user = me()) {
            return;
        }

        $roles = explode('|', $roles);

        foreach ($roles as $handle) {
            // Get the role
            if (! $role = Role::findByHandle($handle)) {
                // If the role doesn't exist, we'll log an error and move on.
                \Log::error("Role [$handle] doesn't exist");
                continue;
            }

            if ($user->hasRole($role->uuid())) {
                return true;
            }
        }

        return false;
    }
}
