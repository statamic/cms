<?php

namespace Statamic\StaticCaching\NoCache;

use Closure;
use Statamic\Facades\Site;
use Statamic\Http\Middleware\Localize;

class NoCacheLocalize extends Localize
{
    public function handle($request, Closure $next)
    {
        Site::resolveCurrentUrlUsing(fn () => $request->get('url'));

        return parent::handle($request, $next);
    }
}
