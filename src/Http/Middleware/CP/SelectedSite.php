<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\Site;
use Statamic\Facades\User;

class SelectedSite
{
    public function handle($request, Closure $next)
    {
        if (User::current()->cant('view', Site::selected())) {
            Site::setSelected(Site::authorized()->first()->handle());
        }

        return $next($request);
    }
}
