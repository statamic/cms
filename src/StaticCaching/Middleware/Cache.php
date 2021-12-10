<?php

namespace Statamic\StaticCaching\Middleware;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache as FacadesCache;
use Statamic\Facades\Antlers;
use Statamic\Statamic;
use Statamic\StaticCaching\Cacher;
use Statamic\Support\Arr;

class Cache
{
    /**
     * @var Cacher
     */
    private $cacher;

    public function __construct(Cacher $cacher)
    {
        $this->cacher = $cacher;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->canBeCached($request) && $this->cacher->hasCachedPage($request)) {
            $response = response($this->cacher->getCachedPage($request));

            $response = $this->replaceNoCache($response);

            return $response;
        }

        $response = $next($request);

        if ($this->shouldBeCached($request, $response)) {
            $this->cacher->cachePage($request, $response);
        }

        return $response;
    }

    private function canBeCached($request)
    {
        if ($request->method() !== 'GET') {
            return false;
        }

        if (Statamic::isCpRoute()) {
            return false;
        }

        return true;
    }

    private function shouldBeCached($request, $response)
    {
        // Only GET requests should be cached. For instance, Live Preview hits frontend URLs as
        // POST requests to preview the changes. We don't want those to trigger any caching,
        // or else pending changes will be shown immediately, even without hitting save.
        if ($request->method() !== 'GET') {
            return false;
        }

        // Draft and private pages should not be cached.
        if ($response->headers->has('X-Statamic-Draft') || $response->headers->has('X-Statamic-Private')) {
            return false;
        }

        if ($response->getStatusCode() !== 200 || $response->getContent() == '') {
            return false;
        }

        return true;
    }

    private function replaceNoCache(Response $response)
    {
        $content = preg_replace_callback('/__STATIC_NOCACHE_(.*)__/', function ($matches) use ($response) {
            $key = $matches[1];
            $cached = FacadesCache::get('nocache-tag-'.$key);
            $context = unserialize($cached);
            $content = Arr::pull($context, '__content');

            return Antlers::parse($content, $context);
        }, $response->getContent());

        $response->setContent($content);

        return $response;
    }
}
