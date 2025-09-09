<?php

namespace Statamic\Facades\CP;

use Illuminate\Support\Facades\Facade;
use Statamic\CP\Icons\IconManager;
use Statamic\CP\Icons\IconSet;

/**
 * @method static IconSet get(string $name)
 *
 * @see \Statamic\CP\Icons\IconManager
 */
class Icon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return IconManager::class;
    }
}
