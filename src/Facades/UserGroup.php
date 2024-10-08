<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Auth\UserGroupRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static null|\Statamic\Contracts\Auth\UserGroup find($id)
 * @method static void save(\Statamic\Contracts\Auth\UserGroup $group)
 * @method static void delete(\Statamic\Contracts\Auth\UserGroup $group)
 * @method static \Statamic\Contracts\Auth\UserGroup make()
 * @method static \Statamic\Fields\Blueprint blueprint()
 *
 * @see \Statamic\Contracts\Auth\UserGroupRepository
 * @see \Statamic\Auth\UserGroup
 */
class UserGroup extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UserGroupRepository::class;
    }
}
