<?php

namespace Statamic\Http\Controllers\CP\Auth\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

trait GetsReferrerUrl
{
    protected function getReferrerUrl(Request $request)
    {
        $sessionReferer = session()->get('two_factor_referer', null);

        session()->put('two_factor_referer', null);

        if ($sessionReferer) {
            return $sessionReferer;
        }

        $url = url()->previous();

        $route = collect(Route::getRoutes())->first(function (\Illuminate\Routing\Route $route) use ($url) {
            return $route->matches(request()->create($url), false);
        });

        $internalRoutes = [
            'statamic.cp.two-factor.setup',
            'statamic.cp.two-factor.confirm',
            'statamic.cp.two-factor.complete',
            'statamic.cp.two-factor.challenge',
            'statamic.cp.two-factor.challenge.attempt',
//            'statamic.cp.users.two-factor.enable',
            'statamic.cp.users.two-factor.recovery-codes.show',
            'statamic.cp.users.two-factor.recovery-codes.generate',
            'statamic.cp.users.two-factor.unlock',
            'statamic.cp.users.two-factor.reset',
        ];

        if (! $route || in_array($route->getName(), $internalRoutes)) {
            // there is no route, or it is a Statamic Two Factor-defined route (and we don't want to redirect there)
            return null;
        }

        return $url;
    }
}
