<?php

namespace Statamic\Facades;

use Statamic\Support\Comparator;
use Illuminate\Support\Facades\Facade;

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
