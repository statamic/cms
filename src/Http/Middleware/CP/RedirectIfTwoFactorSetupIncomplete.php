<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Illuminate\Http\Request;

class RedirectIfTwoFactorSetupIncomplete
{
    public function handle(Request $request, Closure $next)
    {
        if (
            $request->user()->isTwoFactorAuthenticationRequired()
            && ! $request->user()->hasEnabledTwoFactorAuthentication()
        ) {
            return redirect()->route('statamic.cp.two-factor-setup', [
                'referer' => $request->fullUrl(),
            ]);
        }

        return $next($request);
    }
}
