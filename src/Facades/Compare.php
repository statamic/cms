<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Support\Comparator;

/**
 * @method static int values($one, $two)
 * @method static int strings(string $one, string $two)
 * @method static int numbers($one, $two)
 *
 * @see \Statamic\Support\Comparator
 */
class Compare extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Comparator::class;
    }
}
