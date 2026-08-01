<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Sidecar\Driver;
use Statamic\Sidecar\Manager;

/**
 * @method static \Statamic\Sidecar\Manager extend(string $driver, \Closure $callback)
 * @method static bool hasDriver(string $driver)
 * @method static array registeredDrivers()
 * @method static Driver driver(string $collectionHandle)
 * @method static \Illuminate\Support\Collection collections()
 * @method static \Illuminate\Support\Collection handles()
 * @method static bool manages(string $collectionHandle)
 * @method static \Illuminate\Support\Collection packages()
 * @method static void boot()
 *
 * @see \Statamic\Sidecar\Manager
 *
 * @experimental
 */
class Sidecar extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
