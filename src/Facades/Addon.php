<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Extend\AddonRepository;
use Illuminate\Support\Collection;

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
