<?php

namespace Statamic\StaticCaching\Middleware;

use Closure;
use Statamic\Statamic;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\NoCache\NoCacheManager;
use Statamic\StaticCaching\ResponseReplacer;

class Cache
{
    /**
     * @var Cacher
     */
    private $cacher;

    /**
     * @var NoCacheManager
     */
    private $noCacheManager;

    private $replacer;

    public function __construct(Cacher $cacher, NoCacheManager $noCacheManager, ResponseReplacer $replacer)
    {
        $this->cacher = $cacher;
        $this->noCacheManager = $noCacheManager;
        $this->replacer = $replacer;
    }

    private function getPreparedResponseFromCache($request)
    {
        $responseToReturn = response($this->cacher->getCachedPage($request));
        $this->replacer->replaceInResponse($responseToReturn);

        return $responseToReturn;
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
        $noCacheCanHandle = $this->noCacheManager->canHandle($request);

        if (!$noCacheCanHandle && $this->canBeCached($request) && $this->cacher->hasCachedPage($request)) {
            return $this->getPreparedResponseFromCache($request);
        }

        if ($noCacheCanHandle) {
            NoCacheManager::$isRehydrated = true;
            $noCacheResponse = response($this->noCacheManager->restoreSession($request));
            NoCacheManager::reset();

            $this->replacer->replaceInResponse($noCacheResponse);

            return $noCacheResponse;
        }

        if ($this->canBeCached($request) && $this->cacher->hasCachedPage($request)) {
            return $this->getPreparedResponseFromCache($request);
        }

        $response = $next($request);

        if ($this->shouldBeCached($request, $response)) {
            // Create a clone to not impact the outgoing response.
            $responseToCache = clone $response;

            $this->replacer->prepareForCache($responseToCache);

            $this->cacher->cachePage($request, $responseToCache);
            NoCacheManager::reset();
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
}
