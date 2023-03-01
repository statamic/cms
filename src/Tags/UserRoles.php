<?php

namespace Statamic\Tags;

use Statamic\Facades\Role;

class UserRoles extends Tags
{
    /**
     * {{ user_roles }} ... {{ /user_roles }}.
     */
    public function index()
    {
        $roles = Role::all();

        if (! is_array($handles = $this->params->get('handle'))) {
            $handles = $this->params->explode('handle');
        }

        if (empty($handles)) {
            return $roles->values();
        }

        return $roles->filter(fn ($role) => in_array($role->handle(), $handles))->values();
    }
}
