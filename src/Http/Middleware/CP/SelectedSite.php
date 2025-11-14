<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\Site;
use Statamic\Facades\User;

class SelectedSite
{
    public function handle($request, Closure $next)
    {
        $this->selectFromRequest($request);

        $this->selectFromAuth();

        return $next($request);
    }

    private function selectFromAuth()
    {
        if (User::current()->can('view', Site::selected())) {
            return;
        }

        if ($first = Site::authorized()->first()) {
            Site::setSelected($first->handle());
        }
    }

    private function selectFromRequest($request)
    {
        // If the session already has a selected site, don't override it.
        if (session('statamic.cp.selected-site')) {
            return;
        }

        if ($siteByUrl = Site::findByUrl($request->getSchemeAndHttpHost())) {
            Site::setSelected($siteByUrl->handle());
        }
    }
}
