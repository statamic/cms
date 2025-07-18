<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static void enforceTrailingSlashes(bool $bool = true)
 * @method static bool isEnforcingTrailingSlashes()
 * @method static string tidy(?string $url, ?bool $external = false, ?bool $withTrailingSlash = null)
 * @method static string assemble(?string ...$segments)
 * @method static ?string slug(?string $url)
 * @method static string replaceSlug(?string $url, string $slug)
 * @method static string parent(?string $url)
 * @method static bool isAncestorOf(?string $child, ?string $ancestor)
 * @method static string prependSiteUrl(?string $url, ?string $locale = null, bool $controller = true)
 * @method static string removeSiteUrl(?string $url)
 * @method static string makeRelative(?string $url)
 * @method static string makeAbsolute(?string $url)
 * @method static string getCurrent()
 * @method static bool isAbsolute(?string $url)
 * @method static bool isExternal(?string $url)
 * @method static bool isExternalToApplication(?string $url)
 * @method static string encode(?string $url)
 * @method static string gravatar(string $email, ?int $size = null)
 * @method static ?string removeQueryAndFragment(?string $url)
 * @method static void clearUrlCache()
 *
 * @see \Statamic\Facades\Endpoint\Url
 */
class URL extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Endpoint\URL::class;
    }
}
