<?php

namespace Statamic\StaticCaching;

use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

interface Cacher
{
    /**
     * Cache a page.
     *
     * @param \Illuminate\Http\Request $request     Request associated with the page to be cached
     * @param string                   $content     The response content to be cached
     */
    public function cachePage(Request $request, $content);

    /**
     * Check if a page can be cached.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function canBeCached(Request $request);

    /**
     * Check if a page has been cached.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function hasCachedPage(Request $request);

    /**
     * Check if a page should be cached.
     *
     * @param \Illuminate\Http\Request $request
     * @param Response $response
     * @return bool
     */
    public function shouldBeCached(Request $request, Response $response);

    /**
     * Get a cached page.
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function getCachedPage(Request $request);

    /**
     * Flush out the entire static cache.
     *
     * @return void
     */
    public function flush();

    /**
     * Invalidate a URL.
     *
     * @param string $url
     * @return void
     */
    public function invalidateUrl($url);

    /**
     * Invalidate multiple URLs.
     *
     * @param array $urls
     * @return void
     */
    public function invalidateUrls($urls);

    /**
     * Get all the URLs that have been cached.
     *
     * @param string|null $domain
     * @return \Illuminate\Support\Collection
     */
    public function getUrls($domain = null);

    /**
     * Check if the cache can be bypassed.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function canBeBypassed(Request $request);

    /**
     * Check if the cache should be bypassed.
     *
     * @param \Illuminate\Http\Request $request
     * @return bool
     */
    public function shouldBeBypassed(Request $request);

    /**
     * Return the response from the cache, or dont.
     *
     * @param \Illuminate\Http\Request $request
     * @param Response $response
     * @return Response
     */
    public function response(Request $request, Response $response);
}
