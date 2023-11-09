<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Tasks\Tasks;

/**
 * @method static array run($key, callable $callable)
 *
 * @see Statamic\Tasks
 */
class Task extends Facade
{
    protected static function getFacadeAccessor()
    {
        return get_class(app()[Tasks::class]);
    }
}
