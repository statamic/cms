<?php

namespace Statamic\Addons\In;

use Statamic\Facades\User;
use Statamic\Extend\API;
use Statamic\Facades\UserGroup;

class InAPI extends API
{
    public function in($groups)
    {
        // Not logged in? This is the end of the road.
        if (! $user = User::current()) {
            return;
        }

        $groups = explode('|', $groups);

        foreach ($groups as $handle) {
            // Get the group
            if (! $group = UserGroup::findByHandle($handle)) {
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
