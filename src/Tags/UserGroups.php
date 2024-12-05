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

        if (! is_array($handles = $this->params->get('handle'))) {
            $handles = $this->params->explode('handle');
        }

        if (empty($handles)) {
            return $this->aliasedResult($groups->values());
        }

        return $this->aliasedResult($groups->filter(fn ($group) => in_array($group->handle(), $handles))->values());
    }
}
