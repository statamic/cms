<?php

namespace Statamic\StaticCaching\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Statamic\Facades\File;
use Statamic\Statamic;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\NullCacher;
use Statamic\StaticCaching\NoCache\RegionNotFound;
use Statamic\StaticCaching\NoCache\Session;
use Statamic\StaticCaching\Replacer;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\NoLock;
use Symfony\Component\Lock\Store\FlockStore;

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
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $lock = $this->createLock($request);

        while (! $lock->acquire()) {
            sleep(1);
        }

        try {
            if ($response = $this->attemptToGetCachedResponse($request)) {
                return $response;
            }
        } catch (RegionNotFound $e) {
            Log::debug("Static cache region [{$e->getRegion()}] not found on [{$request->fullUrl()}]. Serving uncached response.");
        }

        $response = $next($request);

        if ($this->shouldBeCached($request, $response)) {
            $lock->acquire(true);

            $this->makeReplacementsAndCacheResponse($request, $response);

            $this->nocache->write();
        } elseif (! $response->isRedirect()) {
            $this->makeReplacements($response);
        }

        return $response;
    }

    private function attemptToGetCachedResponse($request)
    {
        if ($this->canBeCached($request) && $this->cacher->hasCachedPage($request)) {
            $cachedPage = $this->cacher->getCachedPage($request);
            $response = $cachedPage->toResponse($request);

            $this->makeReplacements($response);

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

        if ($response->getStatusCode() !== 200 || $response->getContent() == '') {
            return false;
        }

        if ($request->statamicToken()) {
            return false;
        }

        return true;
    }

    private function createLock($request)
    {
        if ($this->cacher instanceof NullCacher) {
            return new NoLock;
        }

        File::makeDirectory($dir = storage_path('statamic/static-caching-locks'));

        $locks = new LockFactory(new FlockStore($dir));

        $key = $this->cacher->getUrl($request);

        return $locks->createLock($key, 30);
    }
}
