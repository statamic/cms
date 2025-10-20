<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RedirectAbsoluteDomains
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $host = $request->getHost();

        if (! Str::endsWith($host, '.')) {
            return $next($request);
        }

        return redirect()->to(Str::replaceFirst($host, rtrim($host, '.'), $request->fullUrl()), 308);
    }
}
