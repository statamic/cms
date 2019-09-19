<?php

namespace Statamic\Auth\File;

use Statamic\Auth\UserGroupRepository as BaseRepository;

class UserGroupRepository extends BaseRepository
{
    public function make()
    {
        return new UserGroup;
    }
}