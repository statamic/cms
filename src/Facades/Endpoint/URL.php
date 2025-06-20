<?php

namespace Statamic\Facades\Endpoint;

use Illuminate\Support\Collection;
use Statamic\Facades\Config;
use Statamic\Facades\Path;
use Statamic\Facades\Site;
use Statamic\Support\Str;

/**
 * Manipulate URLs.
 */
class URL
{
    private static $enforceTrailingSlashes = false;
    private static $absoluteSiteUrlsCache;
    private static $externalSiteUrlsCache;
    private static $externalAppUrlsCache;

    /**
     * Configure whether or not to enforce trailing slashes when normalizing URL output throughout this class.
     */
    public function enforceTrailingSlashes(bool $bool = true): void
    {
        self::$enforceTrailingSlashes = $bool;
    }

    /**
     * Tidy a URL (normalize slashes).
     */
    public function tidy(?string $url): string
    {
        // Remove occurrences of '//', except when part of protocol.
        $url = Path::tidy($url);

        // If URL is external to this Statamic application, we'll avoid normalizing leading/trailing slashes.
        if (self::isAbsolute($url) && self::isExternalToApplication($url)) {
            return $url;
        }

        // If not an absolute URL, enforce leading slash.
        if (! self::isAbsolute($url)) {
            $url = Str::ensureLeft($url, '/');
        }

        // Trim trailing slash, unless enforced with `enforceTrailingSlashes()`.
        $url = self::normalizeTrailingSlash($url);

        return $url;
    }

    /**
     * Assemble a URL from an ordered list of segments.
     */
    public function assemble(?string ...$segments): string
    {
        return self::tidy(Path::assemble($segments));
    }

    /**
     * Get the slug at the end of a URL.
     */
    public function slug(?string $url): ?string
    {
        $url = Str::ensureRight(self::removeQueryAndFragment($url), '/');

        if (parse_url($url)['path'] === '/') {
            return null;
        }

        return basename(self::removeQueryAndFragment($url));
    }

    /**
     * Replace the slug at the end of a URL with the provided slug.
     */
    public function replaceSlug(?string $url, string $slug): string
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
     */
    public function parent(?string $url): string
    {
        $url = Str::ensureRight(self::removeQueryAndFragment($url), '/');

        if (parse_url($url)['path'] === '/') {
            return self::tidy($url);
        }

        $url = preg_replace('/[^\/]*\/$/', '', $url);

        return self::tidy($url);
    }

    /**
     * Check if one URL is an ancestor of another.
     */
    public function isAncestorOf(?string $child, ?string $ancestor): bool
    {
        $child = Str::ensureRight(self::removeQueryAndFragment($child), '/');
        $ancestor = Str::ensureRight(self::removeQueryAndFragment($ancestor), '/');

        if ($child === $ancestor) {
            return false;
        }

        return Str::startsWith($child, $ancestor);
    }

    /**
     * Prepend site URL to a URL.
     */
    public function prependSiteUrl(?string $url, ?string $locale = null, bool $controller = true): string
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
     * Remove current site URL from the beginning of a URL.
     */
    public function removeSiteUrl(?string $url): string
    {
        return self::tidy(preg_replace('#^'.Config::getSiteUrl().'#', '/', $url));
    }

    /**
     * Make an absolute URL relative (with leading slash).
     */
    public function makeRelative(?string $url): string
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
     * Make a relative URL absolute (prepends domain if not already absolute).
     */
    public function makeAbsolute(?string $url): string
    {
        // If URL is external to this Statamic application, we'll just leave it as-is.
        if (self::isAbsolute($url) && self::isExternalToApplication($url)) {
            return $url;
        }

        if (self::isAbsolute($url)) {
            return self::tidy($url);
        }

        $url = Str::ensureLeft($url, '/');
        $url = Str::ensureLeft($url, self::getRequestRootUrl());

        return self::tidy($url);
    }

    /**
     * Get the current URL.
     */
    public function getCurrent(): string
    {
        return self::tidy(request()->path());
    }

    /**
     * Check whether a URL is absolute.
     */
    public function isAbsolute(?string $url): bool
    {
        return Str::startsWith($url, ['http:', 'https:']);
    }

    /**
     * Check whether a URL is external to current site.
     */
    public function isExternal(?string $url): bool
    {
        if (isset(self::$externalSiteUrlsCache[$url])) {
            return self::$externalSiteUrlsCache[$url];
        }

        if (! $url) {
            return false;
        }

        $url = Str::ensureRight($url, '/');

        if (Str::startsWith($url, ['/', '?', '#'])) {
            return self::$externalSiteUrlsCache[$url] = false;
        }

        $isExternal = ! Str::startsWith($url, Str::ensureRight(Site::current()->absoluteUrl(), '/'));

        return self::$externalSiteUrlsCache[$url] = $isExternal;
    }

    /**
     * Check whether a URL is external to whole Statamic application.
     */
    public function isExternalToApplication(?string $url): bool
    {
        if (isset(self::$externalAppUrlsCache[$url])) {
            return self::$externalAppUrlsCache[$url];
        }

        if (! $url) {
            return false;
        }

        $url = Str::ensureRight($url, '/');

        if (Str::startsWith($url, ['/', '?', '#'])) {
            return self::$externalAppUrlsCache[$url] = false;
        }

        $isExternalToSites = self::getAbsoluteSiteUrls()
            ->filter(fn ($siteUrl) => Str::startsWith($url, $siteUrl))
            ->isEmpty();

        $isExternalToCurrentRequestDomain = ! Str::startsWith($url, Str::ensureRight(url()->to('/'), '/'));

        return self::$externalAppUrlsCache[$url] = $isExternalToSites && $isExternalToCurrentRequestDomain;
    }

    /**
     * Encode a URL.
     */
    public function encode(?string $url): string
    {
        $dontEncode = [
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

        return self::tidy(strtr(rawurlencode($url), $dontEncode));
    }

    /**
     * Return a gravatar image URL for an email address.
     */
    public function gravatar(string $email, ?int $size = null): string
    {
        $url = 'https://www.gravatar.com/avatar/'.e(md5(strtolower($email)));

        if ($size) {
            $url .= '?s='.$size;
        }

        return $url;
    }

    /**
     * Remove query and fragment from end of URL.
     */
    public function removeQueryAndFragment(?string $url): ?string
    {
        $url = Str::before($url, '?'); // Remove query params
        $url = Str::before($url, '#'); // Remove anchor fragment

        return self::tidy($url);
    }

    /**
     * Clear URL property caches.
     */
    public function clearUrlCache(): void
    {
        self::$absoluteSiteUrlsCache = null;
        self::$externalSiteUrlsCache = null;
        self::$externalAppUrlsCache = null;
    }

    /**
     * Normalize trailing slash before query and fragment (trims by default, but can be enforced).
     */
    private function normalizeTrailingSlash(?string $url): string
    {
        $parts = str($url)
            ->split(pattern: '/([?#])/', flags: PREG_SPLIT_DELIM_CAPTURE)
            ->all();

        $url = array_shift($parts);
        $queryAndFragments = implode($parts);

        if (in_array($url, ['', '/'])) {
            $url = '/';
        } elseif (self::$enforceTrailingSlashes) {
            $url = Str::ensureRight($url, '/');
        } else {
            $url = Str::removeRight($url, '/');
        }

        return $url.$queryAndFragments;
    }

    /**
     * Get and cache absolute site URLs for external checks.
     */
    private function getAbsoluteSiteUrls(): Collection
    {
        if (self::$absoluteSiteUrlsCache) {
            return self::$absoluteSiteUrlsCache;
        }

        return self::$absoluteSiteUrlsCache = Site::all()
            ->map(fn ($site) => $site->rawConfig()['url'] ?? null)
            ->filter(fn ($siteUrl) => self::isAbsolute($siteUrl))
            ->map(fn ($siteUrl) => Str::ensureRight($siteUrl, '/'));
    }

    /**
     * Get the current root URL from request headers.
     */
    private function getRequestRootUrl(): string
    {
        $rootUrl = url()->to('/');

        return self::tidy($rootUrl);
    }
}
