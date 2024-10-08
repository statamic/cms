<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Http\Request;
use Statamic\StaticCaching\Cacher;

class NullCacher implements Cacher
{
    public function config($key, $default = null)
    {
        return $default;
    }

    public function cachePage(Request $request, $content)
    {
        //
    }

    public function hasCachedPage(Request $request)
    {
        return false;
    }

    public function getCachedPage(Request $request)
    {
        //
    }

    public function flush()
    {
        //
    }

    public function invalidateUrls($urls)
    {
        //
    }

    public function invalidateUrl($url)
    {
        //
    }

    public function getUrls($domain = null)
    {
        return collect();
    }

    public function getBaseUrl()
    {
        return '/';
    }

    public function getUrl(Request $request)
    {
        return $request->getUri();
    }
}
