<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIndexPhpUrls
{
    public function handle(Request $request, Closure $next)
    {
        if (! $request->isMethod('GET') && ! $request->isMethod('HEAD')) {
            return $next($request);
        }

        $urlContainsIndexPhp = $request->getBaseUrl() !== $request->getBasePath();

        if (! $urlContainsIndexPhp) {
            return $next($request);
        }

        return redirect()->to($this->cleanUrl($request), 301);
    }

    private function cleanUrl(Request $request): string
    {
        $query = $request->server('QUERY_STRING');
        $indexPhp = preg_quote(basename($request->getScriptName()), '#');
        $path = preg_replace("#^(/{$indexPhp})+(?=/|$)#", '', $request->getPathInfo()) ?: '/';

        return $request->getSchemeAndHttpHost().$request->getBasePath().$path.($query ? '?'.$query : '');
    }
}
