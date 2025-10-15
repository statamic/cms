<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Auth\RoleRepository;

/**
 * @method static RoleRepository path()
 * @method static \Illuminate\Support\Collection all()
 * @method static null|\Statamic\Contracts\Auth\Role find(string $id)
 * @method static bool exists(string $id)
 * @method static \Statamic\Contracts\Auth\Role make(string $handle = null)
 * @method static void save(\Statamic\Contracts\Auth\Role $role)
 * @method static void delete(\Statamic\Contracts\Auth\Role $role)
 *
 * @see \Statamic\Contracts\Auth\RoleRepository
 * @link \Statamic\Auth\Role
 */
class Role extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RoleRepository::class;
    }
}
