<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Facades\Site;
use Statamic\Statamic;

class AddViewPaths
{
    public function handle($request, Closure $next)
    {
        $finder = view()->getFinder();
        $amp = Statamic::isAmpRequest();
        $site = Site::current()->handle();

        $paths = collect($finder->getPaths())->flatMap(function ($path) use ($site, $amp) {
            return [
                $amp ? $path.'/'.$site.'/amp' : null,
                $path.'/'.$site,
                $amp ? $path.'/amp' : null,
                $path,
            ];
        })->filter()->values()->all();

        $finder->setPaths($paths);

        foreach ($finder->getHints() as $namespace => $paths) {
            foreach ($paths as $path) {
                $finder->prependNamespace($namespace, $path.'/'.$site);
            }
        }

        return $next($request);
    }
}
