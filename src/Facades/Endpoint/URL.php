<?php

namespace Statamic\Facades\Endpoint;

use Statamic\Data\Services\ContentService;
use Statamic\Facades\Config;
use Statamic\Facades\Path;
use Statamic\Facades\Pattern;
use Statamic\Facades\Site;
use Statamic\Support\Str;

/**
 * Manipulate URLs.
 */
class URL
{
    /**
     * Removes occurrences of "//" in a $path (except when part of a protocol)
     * Alias of Path::tidy().
     *
     * @param  string  $url  URL to remove "//" from
     * @return string
     */
    public function tidy($url)
    {
        return Path::tidy($url);
    }

    /**
     * Assembles a URL from an ordered list of segments.
     *
     * @param mixed string  Open ended number of arguments
     * @return string
     */
    public function assemble($args)
    {
        $args = func_get_args();

        return Path::assemble($args);
    }

    /**
     * Get the slug of a URL.
     *
     * @param  string  $url  URL to parse
     * @return string
     */
    public function slug($url)
    {
        return basename($url);
    }

    /**
     * Swaps the slug of a $url with the $slug provided.
     *
     * @param  string  $url  URL to modify
     * @param  string  $slug  New slug to use
     * @return string
     */
    public function replaceSlug($url, $slug)
    {
        return Path::replaceSlug($url, $slug);
    }

    /**
     * Get the parent URL.
     *
     * @param  string  $url
     * @return string
     */
    public function parent($url)
    {
        $url_array = explode('/', $url);
        array_pop($url_array);

        $url = implode('/', $url_array);

        return ($url == '') ? '/' : $url;
    }

    /**
     * Checks if one URL is an ancestor of another.
     */
    public function isAncestorOf($child, $ancestor)
    {
        $child = Str::before($child, '?');
        $child = Str::ensureRight($child, '/');
        $ancestor = Str::ensureRight($ancestor, '/');

        if ($child === $ancestor) {
            return false;
        }

        return Str::startsWith($child, $ancestor);
    }

    /**
     * Make sure the site root is prepended to a URL.
     *
     * @param  string  $url
     * @param  string|null  $locale
     * @param  bool  $controller
     * @return string
     */
    public function prependSiteRoot($url, $locale = null, $controller = true)
    {
        // Backwards compatibility fix:
        // 2.1 added the $locale argument in the second position to match prependSiteurl.
        // Before 2.1, the second argument was controller. We'll handle that here.
        if ($locale === true || $locale === false) {
            $controller = $locale;
            $locale = null;
        }

        return self::makeRelative(
            self::prependSiteUrl($url, $locale, $controller)
        );
    }

    /**
     * Make sure the site root url is prepended to a URL.
     *
     * @param  string  $url
     * @param  string|null  $locale
     * @param  bool  $controller
     * @return string
     */
    public function prependSiteUrl($url, $locale = null, $controller = true)
    {
        $prepend = rtrim(Config::getSiteUrl($locale), '/');

        // If we don't want the front controller, we'll have to strip
        // it out since it should be in the site URL already.
        if (! $controller) {
            // The controller file is index.php in 99% of cases but hey might as well be sure.
            $file = pathinfo(request()->getScriptName())['basename'];
            $prepend = Str::removeRight($prepend, $file);
        }

        $prepend = Str::ensureRight($prepend, '/');

        return Str::ensureLeft(ltrim($url, '/'), $prepend);
    }

    /**
     * Removes the site root url from the beginning of a URL.
     *
     * @param  string  $url
     * @return string
     */
    public function removeSiteUrl($url)
    {
        return preg_replace('#^'.Config::getSiteUrl().'#', '/', $url);
    }

    /**
     * Make an absolute URL relative.
     *
     * @param  string  $url
     * @return string
     */
    public function makeRelative($url)
    {
        $parsed = parse_url($url);

        $url = $parsed['path'] ?? '/';

        if (isset($parsed['query'])) {
            $url .= '?'.$parsed['query'];
        }

        if (isset($parsed['fragment'])) {
            $url .= '#'.$parsed['fragment'];
        }

        return $url;
    }

    /**
     * Make a relative URL absolute.
     *
     * @param  string  $url
     * @return string
     */
    public function makeAbsolute($url)
    {
        // If it doesn't start with a slash, we'll just leave it as-is.
        if (! Str::startsWith($url, '/')) {
            return $url;
        }

        return self::tidy(Str::ensureLeft($url, self::getSiteUrl()));
    }

    /**
     * Get the current URL.
     *
     * @return string
     */
    public function getCurrent()
    {
        return self::format(app('request')->path());
    }

    /**
     * Formats a URL properly.
     *
     * @param  string  $url
     * @return string
     */
    public function format($url)
    {
        return self::tidy('/'.trim($url, '/'));
    }

    /**
     * Checks whether a URL is external or not.
     *
     * @param  string  $url
     * @return bool
     */
    public function isExternal($url)
    {
        if (! $url || Str::startsWith($url, ['/', '#'])) {
            return false;
        }

        return ! Pattern::startsWith(
            Str::ensureRight($url, '/'),
            Site::current()->absoluteUrl()
        );
    }

    /**
     * Get the current site url from Apache headers.
     *
     * @return string
     */
    public function getSiteUrl()
    {
        if (app()->runningInConsole()) {
            return config('app.url');
        }

        $rootUrl = app('request')->root();

        return Str::ensureRight($rootUrl, '/');
    }

    /**
     * Encode a URL.
     *
     * @param  string  $url
     * @return string
     */
    public function encode($url)
    {
        $dont_encode = [
            '%2F' => '/',
            '%40' => '@',
            '%3A' => ':',
            '%3B' => ';',
            '%2C' => ',',
            '%3D' => '=',
            '%2B' => '+',
            '%21' => '!',
            '%2A' => '*',
            '%7C' => '|',
            '%3F' => '?',
            '%26' => '&',
            '%23' => '#',
            '%25' => '%',
        ];

        return strtr(rawurlencode($url), $dont_encode);
    }

    /**
     * Given a localized URI, get the default URI.
     *
     * @param  string  $locale  The locale of the provided URI
     * @param  string  $uri  The URI from which to find the default
     */
    public function getDefaultUri($locale, $uri)
    {
        return $uri; // TODO

        return app(ContentService::class)->defaultUri($locale, $uri);
    }

    /**
     * Return a gravatar image.
     *
     * @param  string  $email
     * @param  int  $size
     * @return string
     */
    public function gravatar($email, $size = null)
    {
        $url = 'https://www.gravatar.com/avatar/'.e(md5(strtolower($email)));

        if ($size) {
            $url .= '?s='.$size;
        }

        return $url;
    }
}
