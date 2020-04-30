<?php

namespace Statamic\Auth\File;

use Statamic\Auth\UserGroup as BaseUserGroup;
use Statamic\Facades\User;

class UserGroup extends BaseUserGroup
{
    public function queryUsers()
    {
        return User::query()->where('groups/'.$this->handle(), true);
    }
}
