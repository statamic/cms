<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Auth\UserGroupRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static null|\Statamic\Contracts\Auth\UserGroup find($id)
 *
 * @see \Statamic\Contracts\Auth\UserGroupRepository
 */
class UserGroup extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UserGroupRepository::class;
    }
}
