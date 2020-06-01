<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed|\Spatie\Blink\Blink store($name = 'default')
 *
 * @see Statamic\Support\Blink
 */
class Blink extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\Support\Blink::class;
    }
}
