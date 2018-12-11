<?php

namespace Statamic\Auth\File;

use Statamic\API\User;
use Statamic\Auth\UserGroup as BaseUserGroup;

class UserGroup extends BaseUserGroup
{
    public function queryUsers()
    {
        return User::query()->whereIn('id', $this->getUserIds());
    }

    protected function getUserIds()
    {
        return $this->users->keys()->all();
    }
}
