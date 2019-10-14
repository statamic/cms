<?php

namespace Statamic\Auth\File;

use Statamic\Facades\User;
use Statamic\Auth\UserGroup as BaseUserGroup;

class UserGroup extends BaseUserGroup
{
    public function queryUsers()
    {
        return User::query()->where('groups/'.$this->handle(), true);
    }
}
