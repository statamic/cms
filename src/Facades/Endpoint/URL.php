<?php

namespace Statamic\Facades\Endpoint;

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
    private static $enforceTrailingSlashes = false;
    private static $siteUrlsCache;
    private static $externalSiteUriCache = [];
    private static $externalAppUriCache = [];

    /**
     * Enforce trailing slashes service provider helper.
     */
    public function enforceTrailingSlashes(bool $bool = true): void
    {
        static::$enforceTrailingSlashes = $bool;
    }

    /**
     * Removes occurrences of "//" in a $path (except when part of a protocol).
     *
     * Also normalizes trailing slash (configurable via `enforceTrailingSlashes()` function).
     *
     * @param  string  $url  URL to remove "//" from
     */
    public function tidy($url): string
    {
        return self::normalizeTrailingSlash(Path::tidy($url));
    }

    /**
     * Assembles a URL from an ordered list of segments.
     *
     * @param mixed string  Open ended number of arguments
     */
    public function assemble(...$segments): string
    {
        return self::tidy(Path::assemble($segments));
    }

    /**
     * Get the slug at the end of a URL.
     *
     * @param  string  $url  URL to parse
     */
    public function slug($url): ?string
    {
        $url = Str::ensureRight(self::removeQueryAndFragment($url), '/');

        if (parse_url($url)['path'] === '/') {
            return null;
        }

        return basename(self::removeQueryAndFragment($url));
    }

    /**
     * Replace the slug at the end of a URL with the provided slug.
     *
     * @param  string  $url  URL to modify
     * @param  string  $slug  New slug to use
     */
    public function replaceSlug($url, $slug): string
    {
        if (parse_url(Str::ensureRight($url, '/'))['path'] === '/') {
            return self::tidy($url);
        }

        $parts = str($url)
            ->split(pattern: '/([?#])/', flags: PREG_SPLIT_DELIM_CAPTURE)
            ->all();

        $url = Str::removeRight(array_shift($parts), '/');
        $queryAndFragments = implode($parts);

        $url = self::tidy(Path::replaceSlug($url, $slug));

        return $url.$queryAndFragments;
    }

    /**
     * Get the parent URL.
     *
     * @param  string  $url
     */
    public function parent($url): string
    {
        $url = Str::ensureRight(self::removeQueryAndFragment($url), '/');

        if (parse_url($url)['path'] === '/') {
            return self::tidy($url);
        }

        $url = preg_replace('/[^\/]*\/$/', '', $url);

        return self::tidy($url);
    }

    /**
     * Checks if one URL is an ancestor of another.
     */
    public function isAncestorOf($child, $ancestor): bool
    {
        $child = Str::ensureRight(self::removeQueryAndFragment($child), '/');
        $ancestor = Str::ensureRight(self::removeQueryAndFragment($ancestor), '/');

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
     */
    public function prependSiteRoot($url, $locale = null, $controller = true): string
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
     */
    public function prependSiteUrl($url, $locale = null, $controller = true): string
    {
        $prepend = Str::removeRight(Config::getSiteUrl($locale), '/');

        // If we don't want the front controller, we'll have to strip
        // it out since it should be in the site URL already.
        if (! $controller) {
            // The controller file is index.php in 99% of cases but hey might as well be sure.
            $file = pathinfo(request()->getScriptName())['basename'];
            $prepend = Str::removeRight($prepend, $file);
        }

        return self::tidy($prepend.'/'.$url);
    }

    /**
     * Removes the site root url from the beginning of a URL.
     *
     * @param  string  $url
     */
    public function removeSiteUrl($url): string
    {
        return self::tidy(preg_replace('#^'.Config::getSiteUrl().'#', '/', $url));
    }

    /**
     * Make an absolute URL relative.
     *
     * @param  string  $url
     */
    public function makeRelative($url): string
    {
        $parsed = parse_url($url);

        $url = $parsed['path'] ?? '/';

        if (isset($parsed['query'])) {
            $url .= '?'.$parsed['query'];
        }

        if (isset($parsed['fragment'])) {
            $url .= '#'.$parsed['fragment'];
        }

        return self::tidy($url);
    }

    /**
     * Make a relative URL absolute.
     *
     * @param  string  $url
     */
    public function makeAbsolute($url): string
    {
        // If it doesn't start with a slash, we'll just leave it as-is.
        if (Str::startsWith($url, ['http:', 'https:']) && self::isExternalToApplication($url)) {
            return $url;
        }

        if (! Str::startsWith($url, '/')) {
            return self::tidy($url);
        }

        return self::tidy(Str::ensureLeft($url, self::getSiteUrl()));
    }

    /**
     * Get the current URL.
     */
    public function getCurrent(): string
    {
        return self::format(app('request')->path());
    }

    /**
     * Formats a URL properly.
     *
     * @param  string  $url
     */
    public function format($url): string
    {
        return self::tidy('/'.trim($url, '/'));
    }

    /**
     * Checks whether a URL is external to current site.
     *
     * @param  string  $url
     */
    public function isExternal($url): bool
    {
        if (isset(self::$externalSiteUriCache[$url])) {
            return self::$externalSiteUriCache[$url];
        }

        if (! $url) {
            return false;
        }

        if (Str::startsWith($url, ['/', '?', '#'])) {
            return self::$externalSiteUriCache[$url] = false;
        }

        $isExternal = ! Pattern::startsWith(
            Str::ensureRight($url, '/'),
            Site::current()->absoluteUrl()
        );

        return self::$externalSiteUriCache[$url] = $isExternal;
    }

    /**
     * Checks whether a URL is external to whole Statamic application.
     *
     * @param  string  $url
     */
    public function isExternalToApplication($url): bool
    {
        if (isset(self::$externalAppUriCache[$url])) {
            return self::$externalAppUriCache[$url];
        }

        if (! $url) {
            return false;
        }

        if (Str::startsWith($url, ['/', '?', '#'])) {
            return self::$externalAppUriCache[$url] = false;
        }

        self::$siteUrlsCache ??= Site::all()
            ->map->url()
            ->filter(fn ($siteUrl) => Str::startsWith($siteUrl, ['http:', 'https:']));

        $isExternal = self::$siteUrlsCache
            ->filter(fn ($siteUrl) => Str::startsWith($url, $siteUrl))
            ->isNotEmpty();

        return self::$externalAppUriCache[$url] = $isExternal;
    }

    public function clearUrlCache()
    {
        self::$siteUrlsCache = null;
        self::$externalSiteUriCache = [];
        self::$externalAppUriCache = [];
    }

    /**
     * Get the current site url from Apache headers.
     */
    public function getSiteUrl(): string
    {
        $rootUrl = url()->to('/');

        return self::tidy($rootUrl, '/');
    }

    /**
     * Encode a URL.
     *
     * @param  string  $url
     */
    public function encode($url): string
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
     * Return a gravatar image.
     *
     * @param  string  $email
     * @param  int  $size
     */
    public function gravatar($email, $size = null): string
    {
        $url = 'https://www.gravatar.com/avatar/'.e(md5(strtolower($email)));

        if ($size) {
            $url .= '?s='.$size;
        }

        return $url;
    }

    /**
     * Remove query and fragment from end of URL.
     *
     * @param  string  $url
     */
    public function removeQueryAndFragment($url): ?string
    {
        $url = Str::before($url, '?'); // Remove query params
        $url = Str::before($url, '#'); // Remove anchor fragment

        return $url;
    }

    /**
     * Normalize trailing slash before query and fragment (trims by default, but can be enforced).
     */
    public function normalizeTrailingSlash(string $url): string
    {
        $parts = str($url)
            ->split(pattern: '/([?#])/', flags: PREG_SPLIT_DELIM_CAPTURE)
            ->all();

        $url = array_shift($parts);
        $queryAndFragments = implode($parts);

        if (in_array($url, ['', '/'])) {
            $url = '/';
        } elseif (static::$enforceTrailingSlashes) {
            $url = Str::ensureRight($url, '/');
        } else {
            $url = Str::removeRight($url, '/');
        }

        return $url.$queryAndFragments;
    }
}
