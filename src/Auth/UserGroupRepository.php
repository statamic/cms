<?php

namespace Statamic\Auth;

use Statamic\Contracts\Auth\UserGroup as UserGroupContract;
use Statamic\Contracts\Auth\UserGroupRepository as RepositoryContract;

abstract class UserGroupRepository implements RepositoryContract
{
    public function find($id): ?UserGroupContract
    {
        return $this->all()->get($id);
    }
}
