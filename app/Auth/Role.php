<?php

namespace Statamic\Auth;

use Statamic\API;
use Statamic\Contracts\Auth\Role as RoleContract;

abstract class Role implements RoleContract
{
    public function editUrl()
    {
        return cp_route('roles.edit', $this->handle());
    }

    public static function __callStatic($method, $parameters)
    {
        return API\Role::{$method}(...$parameters);
    }
}
