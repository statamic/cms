<?php

namespace Statamic\StaticCaching;

use Illuminate\Http\Request;

interface Cacher
{
    /**
     * Cache a page
     *
     * @param \Illuminate\Http\Request $request     Request associated with the page to be cached
     * @param string                   $content     The response content to be cached
     */
    public function cachePage(Request $request, $content);

    /**
     * Get a cached page
     *
     * @param Request $request
     * @return string
     */
    public function getCachedPage(Request $request);

    /**
     * Flush out the entire static cache
     *
     * @return void
     */
    public function flush();

    /**
     * Invalidate a URL
     *
     * @param string $url
     * @return void
     */
    public function invalidateUrl($url);

    /**
     * Invalidate multiple URLs
     *
     * @param array $urls
     * @return void
     */
    public function invalidateUrls($urls);

    /**
     * Get a config value
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function config($key, $default = null);
}
