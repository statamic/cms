<?php

namespace Statamic\Http\Middleware\CP;

use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\User;

class RedirectIfTwoFactorSetupIncomplete
{
    public function handle(Request $request, Closure $next)
    {
        $user = User::fromUser($request->user());

        if (
            $user->isTwoFactorAuthenticationRequired()
            && ! $user->hasEnabledTwoFactorAuthentication()
        ) {
            return redirect()->route('statamic.cp.two-factor-setup', [
                'referer' => $request->fullUrl(),
            ]);
        }

        return $next($request);
    }
}
