<?php

namespace Statamic\API\Endpoint;

use Statamic\Contracts\Auth\UserGroupRepository;
use Statamic\Contracts\Auth\UserGroup as UserGroupContract;

class UserGroup
{
    public function __call($method, $args)
    {
        return call_user_func_array([$this->repo(), $method], $args);
    }

    public function save(UserGroupContract $group)
    {
        $this->repo()->save($group);

        // TODO: UserGroupSaved::dispatch($group);
    }

    protected function repo()
    {
        return app(UserGroupRepository::class);
    }
}
