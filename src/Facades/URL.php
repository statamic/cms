<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string tidy($url)
 * @method static string assemble($args)
 * @method static string slug($url)
 * @method static string replaceSlug($url, $slug)
 * @method static string parent($url)
 * @method static bool isAncestor($parent_uri, $uri = null)
 * @method static string prependSiteRoot($url, $locale = null, $controller = true)
 * @method static string prependSiteUrl($url, $locale = null, $controller = true)
 * @method static string removeSiteUrl($url)
 * @method static string makeRelative($url)
 * @method static string makeAbsolute($url)
 * @method static string getCurrent()
 * @method static string format($url)
 * @method static bool isExternal($url)
 * @method static string getSiteUrl()
 * @method static string encode($url)
 * @method static mixed getDefaultUri($locale, $uri)
 * @method static string gravatar($email, $size = null)
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
