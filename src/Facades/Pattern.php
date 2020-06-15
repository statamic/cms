<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string date()
 * @method static string dateTime()
 * @method static string dateOrDateTime()
 * @method static string numeric()
 * @method static string orderKey()
 * @method static string uuid()
 * @method static bool startsWith($haystack, $needle)
 * @method static bool endsWith($haystack, $needle)
 * @method static bool isUUID($value)
 *
 * @see \Statamic\Facades\Endpoint\Pattern
 */
class Pattern extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Pattern::class;
    }
}
