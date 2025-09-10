<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Icons\IconManager;
use Statamic\Icons\IconSet;

/**
 * @method static IconSet get(string $name)
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
