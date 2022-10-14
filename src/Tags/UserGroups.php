<?php

namespace Statamic\Tags;

use Statamic\Facades\UserGroup;

class UserGroups extends Tags
{
    /**
     * {{ user_groups }} ... {{ /user_groups }}.
     */
    public function index()
    {
        $groups = UserGroup::all();

        if (! $handles = $this->params->explode('handle')) {
            return $groups->values();
        }

        return $groups->filter(fn ($group) => in_array($group->handle(), $handles))->values();
    }
}
