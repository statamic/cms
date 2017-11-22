<?php

namespace Statamic\Addons\In;

use Statamic\API\User;
use Statamic\Extend\API;
use Statamic\API\UserGroup;

class InAPI extends API
{
    public function in($groups)
    {
        // Not logged in? This is the end of the road.
        if (! $user = User::getCurrent()) {
            return;
        }

        $groups = explode('|', $groups);

        foreach ($groups as $handle) {
            // Get the group
            if (! $group = UserGroup::whereHandle($handle)) {
                // If the group doesn't exist, we'll log an error and move on.
                \Log::error("Group [$handle] doesn't exist");
                continue;
            }

            if ($user->inGroup($group->id())) {
                return true;
            }
        }

        return false;
    }
}
