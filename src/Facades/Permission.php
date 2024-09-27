<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Auth\Permissions;

/**
 * @method static void boot()
 * @method static void extend(\Closure $callback)
 * @method static \Statamic\Auth\Permission make(string $value)
 * @method static \Statamic\Auth\Permission register(string $permission, \Closure $callback = null)
 * @method static \Illuminate\Support\Collection all()
 * @method static \Statamic\Auth\Permission get(string $key)
 * @method static \Illuminate\Support\Collection tree()
 * @method static void group(string $name, string $label, $permissions = null)
 *
 * @see \Statamic\Auth\Permissions
 */
class Permission extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Permissions::class;
    }
}
