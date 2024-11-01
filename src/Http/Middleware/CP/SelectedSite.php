<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\Site;
use Statamic\Facades\User;

class SelectedSite
{
    public function handle($request, Closure $next)
    {
        $this->updateSelectedSite($request);

        return $next($request);
    }

    private function updateSelectedSite($request)
    {
        $siteByUrl = Site::findByUrl($request->getSchemeAndHttpHost());

        /* Ensure that we only make this automatic selection when first loggin in */
        if (! session('statamic.cp.selected-site') && $siteByUrl) {
            Site::setSelected($siteByUrl->handle());
        }

        if (User::current()->can('view', Site::selected())) {
            return;
        }

        if ($first = Site::authorized()->first()) {
            Site::setSelected($first->handle());
        }
    }
}
