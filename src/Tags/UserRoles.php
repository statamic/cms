<?php

namespace Statamic\Tags;

use Statamic\Facades\Role;

class UserRoles extends Tags
{
    use Concerns\OutputsItems;

    /**
     * {{ user_roles }} ... {{ /user_roles }}.
     */
    public function index()
    {
        if ($group = $this->params->get('handle')) {
            if (! $group = Role::find($group)) {
                return $this->parseNoResults();
            }

            return $group;
        }

        return $this->output(Role::all()->values());
    }
}
