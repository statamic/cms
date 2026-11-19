<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool enabled()
 *
 * @see \Statamic\Auth\TwoFactor\TwoFactor
 */
class TwoFactor extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Auth\TwoFactor\TwoFactor::class;
    }
}
