<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Statamic\Facades\User;

class Localize
{
    public function handle($request, Closure $next)
    {
        $locale = User::current()->getPreference('locale') ?? app()->getLocale();

        app()->setLocale($locale);

        return $next($request);
    }
}
