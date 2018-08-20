<?php

namespace Statamic\API\Endpoint;

use Statamic\Contracts\Permissions\RoleRepository;

class Role
{
    public function __call($method, $args)
    {
        return call_user_func_array([app(RoleRepository::class), $method], $args);
    }
}
