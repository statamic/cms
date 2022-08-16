<?php

namespace Statamic\Tags;

use Statamic\Facades\UserGroup;

class UserGroups extends Tags
{
    use Concerns\OutputsItems;

    /**
     * {{ user_groups }} ... {{ /user_groups }}.
     */
    public function index()
    {
        if ($group = $this->params->get('handle')) {
            if (! $group = UserGroup::find($group)) {
                return $this->parseNoResults();
            }

            return $group;
        }

        return $this->output(UserGroup::all()->values());
    }
}
