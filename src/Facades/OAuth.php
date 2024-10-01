<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\OAuth\Manager;

/**
 * @method static bool enabled()
 * @method static mixed|\Statamic\OAuth\Provider provider($provider)
 * @method static \Illuminate\Support\Collection providers()
 *
 * @see \Statamic\OAuth\Provider
 */
class OAuth extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
