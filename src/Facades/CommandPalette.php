<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\CommandPalette\Palette;

/**
 * @see \Statamic\CommandPalette\Palette
 */
class CommandPalette extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Palette::class;
    }
}
