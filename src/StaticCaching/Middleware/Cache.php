<?php

namespace Statamic\StaticCaching\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Statamic\Statamic;
use Statamic\StaticCaching\Cacher;
use Statamic\StaticCaching\Cachers\AbstractCacher;

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

        if ($this->canBeBypassed()) {
            return $this->shouldBeBypassed() ? false : true; // Flip (true/false)
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

        if ($this->canBeBypassed()) {
            return $this->shouldBeBypassed() ? false : true; // Flip (true/false)
        }

        return true;
    }

    private function canBeBypassed()
    {
        if ($this->cacher instanceof AbstractCacher) {
            $bypass = $this->cacher->config('bypass', []);

            return ! empty($bypass['roles']) || isset($bypass['logged_in']);
        }

        return false;
    }

    private function shouldBeBypassed()
    {
        if ($this->cacher instanceof AbstractCacher) {
            $bypass = $this->cacher->config('bypass', []);

            if (! Auth::guest()) {
                if (is_array($bypass['roles']) && ! empty($bypass['roles'])) {
                    return Auth::user()->hasRole($bypass['roles']);
                }

                return isset($bypass['logged_in']) ? $bypass['logged_in'] : false;
            }
        }

        return false;
    }
}
