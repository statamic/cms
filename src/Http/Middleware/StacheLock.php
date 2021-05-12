<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Facades\Stache;

class StacheLock
{
    public function handle($request, Closure $next)
    {
        if (! config('statamic.stache.lock.enabled', true)) {
            return $next($request);
        }

        $start = time();
        $lock = Stache::lock('stache-warming');

        while (! $lock->acquire()) {
            if (time() - $start >= config('statamic.stache.lock.timeout', 30)) {
                return $this->outputRefreshResponse($request);
            }

            sleep(1);
        }

        $lock->release();

        return $next($request);
    }

    private function outputRefreshResponse($request)
    {
        $html = $request->ajax() || $request->wantsJson()
            ? __('Service Unavailable')
            : sprintf('<meta http-equiv="refresh" content="1; URL=\'%s\'" />', $request->getUri());

        return response($html, 503, ['Retry-After' => 1]);
    }
}
