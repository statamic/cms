<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Auth\RoleRepository;

/**
 * @method static \Illuminate\Support\Collection all()
 * @method static null|\Statamic\Contracts\Auth\Role find(string $id)
 * @method static bool exists(string $id)
 * @method static \Statamic\Contracts\Auth\Role make(string $handle = null)
 *
 * @see \Statamic\Contracts\Auth\RoleRepository
 */
class Role extends Facade
{
    protected static function getFacadeAccessor()
    {
        return RoleRepository::class;
    }
}
