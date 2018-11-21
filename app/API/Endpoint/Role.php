<?php

namespace Statamic\API\Endpoint;

use Statamic\Contracts\Permissions\RoleRepository;
use Statamic\Contracts\Permissions\Role as RoleContract;

class Role
{
    public function __call($method, $args)
    {
        return call_user_func_array([$this->repo(), $method], $args);
    }

    public function save(RoleContract $role)
    {
        $this->repo()->save($role);

        // TODO: RoleSaved::dispatch($role);
    }

    protected function repo()
    {
        return app(RoleRepository::class);
    }
}
