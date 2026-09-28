<?php

namespace Statamic\Facades;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Facade;
use Statamic\Addons\AddonRepository;

/**
 * @method static \Statamic\Addons\Addon make(string $addon)
 * @method static Collection all()
 * @method static \Statamic\Addons\Addon get(string $id)
 *
 * @see \Statamic\Addons\AddonRepository
 */
class Addon extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AddonRepository::class;
    }
}
