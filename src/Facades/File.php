<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Filesystem\Manager;

/**
 * @method static \Statamic\Filesystem\Filesystem disk($name = null)
 *
 * @see \Statamic\Filesystem\Manager
 */
class File extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
