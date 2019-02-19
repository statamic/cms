<?php

namespace Statamic\Auth;

use Statamic\Contracts\Auth\Role as RoleContract;

abstract class Role implements RoleContract
{
    public function editUrl()
    {
        return cp_route('roles.edit', $this->handle());
    }
}
