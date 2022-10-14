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

        if (! $handles = $this->params->explode('handle')) {
            return $roles->values();
        }

        return $roles->filter(fn ($role) => in_array($role->handle(), $handles))->values();
    }
}
