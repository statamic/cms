<?php

namespace Statamic\API;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Auth\UserGroupRepository;

class UserGroup extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UserGroupRepository::class;
    }
}
