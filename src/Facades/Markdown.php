<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Markdown\Manager;

/**
 * @method static \Statamic\Markdown\Parser makeParser(array $config = [])
 * @method static mixed|\Statamic\Markdown\Parser parser(string $name)
 * @method static bool hasParser(string $name)
 * @method static void extend(string $name, \Closure $closure)
 *
 * @see \Statamic\Markdown\Manager
 */
class Markdown extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Manager::class;
    }
}
