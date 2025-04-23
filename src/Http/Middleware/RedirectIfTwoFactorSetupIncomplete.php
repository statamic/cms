<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectIfTwoFactorSetupIncomplete
{
    public function handle(Request $request, Closure $next)
    {
        if (
            $request->user()
            && $request->user()->isTwoFactorAuthenticationRequired()
            && ! $request->user()->hasEnabledTwoFactorAuthentication()
        ) {
            return redirect()->route('statamic.two-factor-setup', [
                'referer' => $request->fullUrl(),
            ]);
        }

        return $next($request);
    }
}
