<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed parser()
 * @method static mixed usingParser(Parser $parser, Closure $callback)
 * @method static mixed parse($str, $variables = [])
 * @method static string parseLoop($content, $data, $supplement = true, $context = [])
 *
 * @see \Statamic\View\Antlers\Antlers
 */
class Antlers extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \Statamic\View\Antlers\Antlers::class;
    }
}
