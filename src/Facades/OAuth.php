<?php

namespace Statamic\Facades;

use Statamic\OAuth\Manager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool enabled()
 * @method static mixed|\Statamic\OAuth\Provider Providerprovider($provider)
 * @method static mixed providers()
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
