<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Imaging\GlideManager;
use Statamic\Imaging\Manipulators\GlideManipulator;

/**
 * @deprecated Glide should be accessed through its manipulation class.
 * @see GlideManipulator
 */
class Glide extends Facade
{
    protected static function getFacadeAccessor()
    {
        return GlideManager::class;
    }
}
