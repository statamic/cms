<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string makeRelative($path)
 * @method static string makeFull($path)
 * @method static bool isAbsolute($path)
 * @method static string toUrl($path)
 * @method static string resolve($path)
 * @method static string clean($path)
 * @method static string assemble($args)
 * @method static bool isPage($path)
 * @method static bool isEntry($path)
 * @method static string status($path)
 * @method static bool isDraft($path)
 * @method static bool isHidden($path)
 * @method static string tidy($path)
 * @method static string|null extension($path)
 * @method static string directory($path)
 * @method static mixed folder($path)
 * @method static string popLastSegment($path)
 *
 * @see \Statamic\Facades\Endpoint\Path
 */
class Path extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\Path::class;
    }
}
