<?php

namespace Statamic\StaticCaching\Middleware;

use Closure;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache as AppCache;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\StaticCache;
use Statamic\Statamic;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\ApplicationCacher;
use Statamic\StaticCaching\Cachers\NullCacher;
use Statamic\StaticCaching\NoCache\RegionNotFound;
use Statamic\StaticCaching\NoCache\Session;
use Statamic\StaticCaching\Replacer;
use Statamic\StaticCaching\ResponseStatus;

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

    private int $lockFor = 30;

    public function __construct(Cacher $cacher, Session $nocache)
    {
        $this->cacher = $cacher;
        $this->nocache = $nocache;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        try {
            if ($response = $this->attemptToGetCachedResponse($request)) {
                return $response;
            }
        } catch (RegionNotFound $e) {
            Log::debug("Static cache region [{$e->getRegion()}] not found on [{$request->fullUrl()}]. Serving uncached response.");
        }

        $lock = $this->createLock($request);

        return $lock->block($this->lockFor, fn () => $this->handleRequest($request, $next));
    }

    private function handleRequest($request, Closure $next)
    {
        $response = $next($request);

        if ($this->shouldBeCached($request, $response)) {
            $this->copyError($request, $response);

            $this->makeReplacementsAndCacheResponse($request, $response);

            $this->nocache->write();
        } elseif (! $response->isRedirect()) {
            $this->makeReplacements($response);
        }

        return $response;
    }

    private function copyError($request, $response)
    {
        $status = $response->getStatusCode();

        if (! config('statamic.static_caching.share_errors')) {
            return;
        }

        $request = Request::createFrom($request)->fakeStaticCacheStatus($status);

        if (! $this->cacher->hasCachedPage($request)) {
            $this->cacher->cachePage($request, $response);
        }
    }

    private function attemptToGetCachedResponse($request)
    {
        if ($this->canBeCached($request) && $this->cacher->hasCachedPage($request)) {
            $cachedPage = $this->cacher->getCachedPage($request);
            $response = $cachedPage->toResponse($request);

            $this->makeReplacements($response);

            $response->setStaticCacheResponseStatus(ResponseStatus::HIT);

            return $response;
        }
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

        if ($request->statamicToken()) {
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

        $statuses = $this->cacher instanceof ApplicationCacher ? [200, 404] : [200];

        if (! in_array($response->getStatusCode(), $statuses) || $response->getContent() == '') {
            return false;
        }

        if ($request->statamicToken()) {
            return false;
        }

        return true;
    }

    private function createLock($request): Lock
    {
        $key = 'static-cache-lock';

        if ($this->cacher instanceof NullCacher) {
            $store = AppCache::store('null');
        } else {
            $store = StaticCache::cacheStore();
            $key .= '-'.$this->cacher->getUrl($request);
        }

        return $store->lock($key, $this->lockFor);
    }
}
