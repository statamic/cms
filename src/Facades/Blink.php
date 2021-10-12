<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Spatie\Blink\Blink put($key, $value = null)
 * @method static null|string|array get(string $key, $default = null)
 * @method bool has(string $key)
 * @method array all()
 * @method array allStartingWith(string $startingWith = '')
 * @method \Spatie\Blink\Blink forget(string $key)
 * @method \Spatie\Blink\Blink flush()
 * @method \Spatie\Blink\Blink flushStartingWith(string $startingWith = '')
 * @method null|string pull(string $key)
 * @method int|null|string increment(string $key, int $by = 1)
 * @method int|null|string decrement(string $key, int $by = 1)
 * @method bool offsetExists($offset)
 * @method mixed offsetGet($offset)
 * @method void offsetSet($offset, $value)
 * @method void offsetUnset($offset)
 * @method int count()
 * @method mixed once($key, callable $callable)
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
