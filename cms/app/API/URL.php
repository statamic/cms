<?php

namespace Statamic\API;

use Stringy\StaticStringy as Stringy;
use Statamic\Data\Services\ContentService;

/**
 * Manipulate URLs
 */
class URL
{
    /**
     * Removes occurrences of "//" in a $path (except when part of a protocol)
     * Alias of Path::tidy()
     *
     * @param string  $url  URL to remove "//" from
     * @return string
     */
    public static function tidy($url)
    {
        return Path::tidy($url);
    }

    /**
     * Assembles a URL from an ordered list of segments
     *
     * @param mixed string  Open ended number of arguments
     * @return string
     */
    public static function assemble($args)
    {
        $args = func_get_args();

        return Path::assemble($args);
    }

    /**
     * Takes a localized URL and returns the unlocalized, default version
     *
     * @param string      $url    The localized URL to transform
     * @param string|null $locale Optional locale to transform from
     * @return string
     * @deprecated since 2.1
     */
    public static function unlocalize($url, $locale = null)
    {
        return self::getDefaultUri($locale, $url);
    }

    /**
     * Checks whether a URL exists
     *
     * @param string       $url     URL to find
     * @return bool
     */
    public static function exists($url)
    {
        return Content::uriExists($url);
    }

    /**
     * Get the slug of a URL
     *
     * @param string $url  URL to parse
     * @return string
     */
    public static function slug($url)
    {
        return basename($url);
    }

    /**
     * Swaps the slug of a $url with the $slug provided
     *
     * @param string  $url   URL to modify
     * @param string  $slug  New slug to use
     * @return string
     */
    public static function replaceSlug($url, $slug)
    {
        return Path::replaceSlug($url, $slug);
    }

    /**
     * Get the parent URL
     *
     * @param string $url
     * @return string
     */
    public static function parent($url)
    {
        $url_array = explode('/', $url);
        array_pop($url_array);

        $url = implode('/', $url_array);

        return ($url == '') ? '/' : $url;
    }

    /**
     * Check if a URL is an acestor of the current URL
     *
     * @param string        $uri
     * @return boolean
     */
    public static function isAncestor($parent_uri, $uri = null)
    {
        // Homepage would always be ancestor
        // and not what we're looking for here.
        if ($parent_uri === '/') {
            return false;
        }
        
        // We default to the current URL.
        if ($uri === null)
        {
            $uri = self::getCurrent();
        }

        // Add trailing slashes to ensure we're comparing the whole URI
        // and not just pieces of it (e.g. /about and /about-me)
        $uri = Stringy::ensureRight($uri, '/');
        $parent_uri = Stringy::ensureRight($parent_uri, '/');

        return Pattern::startsWith($uri, $parent_uri);
    }

    /**
     * Make sure the site root is prepended to a URL
     *
     * @param  string       $url
     * @param  string|null  $locale
     * @param  boolean      $controller
     * @return string
     */
    public static function prependSiteRoot($url, $locale = null, $controller = true)
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
     * Make sure the site root url is prepended to a URL
     *
     * @param string      $url
     * @param string|null $locale
     * @param bool        $controller
     * @return string
     */
    public static function prependSiteUrl($url, $locale = null, $controller = true)
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
     * Remove the site root from the start of a URL
     *
     * @param string      $url
     * @param string|null $locale
     * @return string
     */
    public static function removeSiteRoot($url, $locale = null)
    {
        return self::tidy('/' . Str::removeLeft($url, SITE_ROOT));
    }

    /**
     * Removes the site root url from the beginning of a URL
     *
     * @param string $url
     * @return string
     */
    public static function removeSiteUrl($url)
    {
        return preg_replace('#^'. Config::getSiteUrl() .'#', '/', $url);
    }

    /**
     * Make an absolute URL relative
     *
     * @param string $url
     * @return string
     */
    public static function makeRelative($url)
    {
        $parsed = parse_url($url);

        $url = $parsed['path'];

        if (isset($parsed['query'])) {
            $url .= '?' . $parsed['query'];
        }

        if (isset($parsed['fragment'])) {
            $url .= '#' . $parsed['fragment'];
        }

        return $url;
    }

    /**
     * Make a relative URL absolute
     *
     * @param string $url
     * @return string
     */
    public static function makeAbsolute($url)
    {
        // If it doesn't start with a slash, we'll just leave it as-is.
        if (! Str::startsWith($url, '/')) {
            return $url;
        }

        return self::tidy(Str::ensureLeft($url, self::getSiteUrl()));
    }

    /**
     * Get the current URL
     *
     * @return string
     */
    public static function getCurrent()
    {
        return format_url(app('request')->path());
    }

    /**
     * Formats a URL properly
     *
     * @param string $url
     * @return string
     */
    public static function format($url)
    {
        return self::tidy(format_url($url));
    }

    /**
     * Checks whether a URL is external or not
     * @param  string  $url
     * @return boolean
     */
    public static function isExternalUrl($url)
    {
        return ! Pattern::startsWith(
            Str::ensureRight($url, '/'),
            self::prependSiteUrl('/')
        );
    }

    /**
     * Get the current site url from Apache headers
     * @return string
     */
    public static function getSiteUrl()
    {
        if (app()->runningInConsole()) {
            return '/';
        }

        $protocol = (! empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443)
            ? "https://"
            : "http://";

        $domain_name = $_SERVER['HTTP_HOST'] . '/';

        return $protocol . $domain_name;
    }

    /**
     * Build a page URL from a path
     *
     * @param string $path
     * @return string
     */
    public static function buildFromPath($path)
    {
        $path = Path::makeRelative($path);

        $ext = pathinfo($path)['extension'];

        $path = Path::clean($path);

        $path = preg_replace('/^pages/', '', $path);

        $path = preg_replace('#\/(?:[a-z]+\.)?index\.'.$ext.'$#', '', $path);

        return Str::ensureLeft($path, '/');
    }

    /**
     * Encode a URL
     *
     * @param string $url
     * @return string
     */
    public static function encode($url)
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
     * Given a localized URI, get the default URI
     *
     * @param string $locale  The locale of the provided URI
     * @param string $uri     The URI from which to find the default
     */
    public static function getDefaultUri($locale, $uri)
    {
        return app(ContentService::class)->defaultUri($locale, $uri);
    }
}
