<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\CP\Utilities\UtilityRepository;

/**
 * @method static self boot()
 * @method static void extend(\Closure $callback)
 * @method static \Statamic\CP\Utilities\Utility make(string $handle)
 * @method static \Statamic\CP\Utilities\Utility register($utility)
 * @method static array all()
 * @method static \Illuminate\Support\Collection authorized()
 * @method static mixed find(string $handle)
 * @method static mixed findBySlug(string $handle)
 * @method static void routes()
 *
 * @see \Statamic\CP\Utilities\UtilityRepository
 */
class Utility extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UtilityRepository::class;
    }
}
