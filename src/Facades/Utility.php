<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\CP\Utilities\UtilityRepository;

/**
 * @method static mixed|null make($handle)
 * @method static \Statamic\CP\Utilities\Utility register($utility)
 * @method static mixed all()
 * @method static mixed authorized()
 * @method static mixed find($handle)
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
