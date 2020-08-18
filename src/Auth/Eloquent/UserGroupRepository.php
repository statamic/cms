<?php

namespace Statamic\Auth\Eloquent;

use Statamic\Auth\File\UserGroupRepository as BaseRepository;

class UserGroupRepository extends BaseRepository
{
    public function make()
    {
        return new UserGroup;
    }
}
