<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\TwoFactor;
use Statamic\Facades\URL;
use Statamic\Facades\User;

class RedirectIfTwoFactorSetupIncomplete
{
    public function handle(Request $request, Closure $next)
    {
        if (
            TwoFactor::enabled()
            && ($user = User::fromUser($request->user()))
            && $user->isTwoFactorAuthenticationRequired()
            && ! $user->hasEnabledTwoFactorAuthentication()
            && ! $this->isSetupUrl($request)
        ) {
            return redirect($this->redirectUrl($request));
        }

        return $next($request);
    }

    protected function isSetupUrl(Request $request): bool
    {
        $currentPath = '/'.ltrim($request->path(), '/');

        // Check if we're on the custom setup URL from session.
        if ($request->hasSession() && ($customUrl = $request->session()->get('login.two_factor_setup_url'))) {
            if (! URL::isExternalToApplication($customUrl)) {
                $customPath = '/'.ltrim(parse_url($customUrl, PHP_URL_PATH), '/');

                if ($currentPath === $customPath) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function redirectUrl(Request $request): string
    {
        if ($request->hasSession() && ($url = $request->session()->get('login.two_factor_setup_url'))) {
            if (! URL::isExternalToApplication($url)) {
                return $url;
            }
        }

        return route($this->redirectRoute(), [
            'referer' => $request->fullUrl(),
        ]);
    }

    protected function redirectRoute(): string
    {
        return 'statamic.two-factor-setup';
    }
}
