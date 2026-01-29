<?php

namespace Statamic\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Statamic\Icons\IconManager;
use Statamic\Icons\IconSet;

/**
 * @method static void register(string $name, string $directory)
 * @method static Collection sets(string $name)
 * @method static IconSet get(string $name)
 * @method static IconSet default()
 *
 * @see \Statamic\Icons\IconManager
 */
class Icon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return IconManager::class;
    }
}
