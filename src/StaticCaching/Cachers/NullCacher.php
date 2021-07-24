<?php

namespace Statamic\StaticCaching\Cachers;

use Illuminate\Http\Request;
use Statamic\StaticCaching\Cacher;
use Symfony\Component\HttpFoundation\Response;

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

    public function canBeCached(Request $request)
    {
        return false;
    }

    public function shouldBeCached(Request $request, Response $response)
    {
        return false;
    }

    public function canBeBypassed()
    {
        return false;
    }

    public function shouldBeBypassed()
    {
        return false;
    }

    public function response(Request $request, Response $response)
    {
        return $response;
    }
}
