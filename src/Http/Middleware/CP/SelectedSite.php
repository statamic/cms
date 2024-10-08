<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\Site;
use Statamic\Facades\User;

class SelectedSite
{
    public function handle($request, Closure $next)
    {
        $this->updateSelectedSite();

        return $next($request);
    }

    private function updateSelectedSite()
    {
        if (User::current()->can('view', Site::selected())) {
            return;
        }

        if ($first = Site::authorized()->first()) {
            Site::setSelected($first->handle());
        }
    }
}
