<?php

namespace Statamic\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Statamic\Extend\AddonRepository;

/**
 * @method static \Statamic\Extend\Addon make(string $addon)
 * @method static Collection all()
 * @method static \Statamic\Extend\Addon get(string $id)
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
