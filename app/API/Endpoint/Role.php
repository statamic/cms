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

    public function create()
    {
        return app(RoleContract::class);
    }

    public function save(RoleContract $role)
    {
        $this->repo()->save($role);

        // TODO: RoleSaved::dispatch($role);
    }

    public function delete(RoleContract $role)
    {
        $this->repo()->delete($role);

        // TODO: RoleDeleted::dispatch();
    }

    protected function repo()
    {
        return app(RoleRepository::class);
    }
}
