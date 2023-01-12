<?php

namespace Statamic\StaticCaching\Middleware;

use Closure;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Statamic\Statamic;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\NoCache\Session;
use Statamic\StaticCaching\Replacer;

class Cache
{
    /**
     * @var Cacher
     */
    private $cacher;

    /**
     * @var Session
     */
    protected $nocache;

    public function __construct(Cacher $cacher, Session $nocache)
    {
        $this->cacher = $cacher;
        $this->nocache = $nocache;
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

            $this->makeReplacements($response);

            return $response;
        }

        $response = $next($request);

        if ($this->shouldBeCached($request, $response)) {
            $this->makeReplacementsAndCacheResponse($request, $response);

            $this->nocache->write();
        } elseif (! $response->isRedirect()) {
            $this->makeReplacements($response);
        }

        return $response;
    }

    private function makeReplacementsAndCacheResponse($request, $response)
    {
        $cachedResponse = clone $response;

        if ($response instanceof Response) {
            $this->getReplacers()->each(fn (Replacer $replacer) => $replacer->prepareResponseToCache($cachedResponse, $response));
        }

        $this->cacher->cachePage($request, $cachedResponse);
    }

    private function makeReplacements($response)
    {
        if ($response instanceof Response) {
            $this->getReplacers()->each(fn (Replacer $replacer) => $replacer->replaceInCachedResponse($response));
        }
    }

    private function getReplacers(): Collection
    {
        return collect(config('statamic.static_caching.replacers'))->map(fn ($class) => app($class));
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
}
