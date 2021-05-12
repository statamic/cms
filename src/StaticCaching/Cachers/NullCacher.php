<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Http\Request;
use Statamic\StaticCaching\Cacher;

class NullCacher implements Cacher
{
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
}
