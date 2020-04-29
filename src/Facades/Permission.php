<?php

namespace Statamic\Facades;

use Statamic\Auth\Permissions;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Statamic\Auth\Permission make(string $value)
 * @method static \Statamic\Auth\Permission register($permission, $callback = null)
 * @method static mixed all()
 * @method static mixed get($key)
 * @method static mixed tree()
 * @method static mixed|null group($name, $label, $permissions = null)
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
