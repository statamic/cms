<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\User;

class RedirectIfTwoFactorSetupIncomplete
{
    public function handle(Request $request, Closure $next)
    {
        if (
            ($user = User::fromUser($request->user()))
            && $user->isTwoFactorAuthenticationRequired()
            && ! $user->hasEnabledTwoFactorAuthentication()
        ) {
            return redirect()->route($this->redirectRoute(), [
                'referer' => $request->fullUrl(),
            ]);
        }

        return $next($request);
    }

    protected function redirectRoute(): string
    {
        return 'statamic.two-factor-setup';
    }
}
