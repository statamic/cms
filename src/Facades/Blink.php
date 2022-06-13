<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Spatie\Blink\Blink put($key, $value = null)
 * @method static null|string|array get(string $key, $default = null)
 * @method static bool has(string $key)
 * @method static array all()
 * @method static array allStartingWith(string $startingWith = '')
 * @method static \Spatie\Blink\Blink forget(string $key)
 * @method static \Spatie\Blink\Blink flush()
 * @method static \Spatie\Blink\Blink flushStartingWith(string $startingWith = '')
 * @method static null|string pull(string $key)
 * @method static int|null|string increment(string $key, int $by = 1)
 * @method static int|null|string decrement(string $key, int $by = 1)
 * @method static bool offsetExists($offset)
 * @method static mixed offsetGet($offset)
 * @method static void offsetSet($offset, $value)
 * @method static void offsetUnset($offset)
 * @method static int count()
 * @method static mixed once($key, callable $callable)
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
