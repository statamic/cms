<?php

namespace Statamic\Http\Middleware;

use Closure;
use Statamic\Console\Composer\Json as ComposerJson;
use Statamic\Exceptions\ComposerJsonMissingPreUpdateCmdException;

class CheckComposerJsonScripts
{
    public function handle($request, Closure $next)
    {
        if (config('app.debug') === false || app()->environment() === 'testing' || $request->is('_ignition*') || $request->wantsJson()) {
            return $next($request);
        }

        if (ComposerJson::isMissingPreUpdateCmd()) {
            throw new ComposerJsonMissingPreUpdateCmdException;
        }

        return $next($request);
    }
}
