<?php

namespace Statamic\StaticCaching\Middleware;

use Closure;
use Statamic\Statamic;
use Statamic\StaticCaching\Cacher;
use Statamic\Tags\NoCache\NoCacheManager;

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

    public function __construct(Cacher $cacher, NoCacheManager $noCacheManager)
    {
        $this->cacher = $cacher;
        $this->noCacheManager = $noCacheManager;
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
            return response($this->cacher->getCachedPage($request));
        }

        if ($noCacheCanHandle) {
            NoCacheManager::$isRehydrated = true;
            return response($this->noCacheManager->restoreSession($request));
        }

        if ($this->canBeCached($request) && $this->cacher->hasCachedPage($request)) {
            return response($this->cacher->getCachedPage($request));
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
}
