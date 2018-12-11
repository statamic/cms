<?php

namespace Statamic\Eloquent\Auth;

use Statamic\Auth\UserGroupRepository as BaseRepository;

class UserGroupRepository extends BaseRepository
{
    public function make()
    {
        return new UserGroup;
    }
}
