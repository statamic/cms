<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string template($str, $variables = [], $context = [], $php = false)
 * @method static string templateLoop($content, $data, $supplement = true, $context = [], $php = false)
 * @method static array YAML($str)
 * @method static mixed env($val)
 *
 * @see \Statamic\Facades\Endpoint\Parse
 */
class Parse extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Parse::class;
    }
}
