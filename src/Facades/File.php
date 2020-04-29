<?php

namespace Statamic\Facades;

use Statamic\Filesystem\Manager;
use Illuminate\Support\Facades\Facade;

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
