<?php

namespace Statamic\Facades;

use Statamic\Extend\AddonRepository;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \Statamic\Extend\Addon make($addon)
 * @method static \Illuminate\Support\Collection all()
 * @method static \Statamic\Extend\Addon get($id)
 *
 * @see \Statamic\Extend\AddonRepository
 */
class Addon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AddonRepository::class;
    }
}
