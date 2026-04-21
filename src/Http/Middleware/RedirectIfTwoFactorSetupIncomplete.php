<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Statamic\Facades\TwoFactor;
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
        if (! $customUrl = config('statamic.users.two_factor_setup_url')) {
            return false;
        }

        $currentPath = '/'.ltrim($request->path(), '/');
        $customPath = '/'.ltrim(parse_url($customUrl, PHP_URL_PATH) ?? '', '/');

        return $currentPath === $customPath;
    }

    protected function redirectUrl(Request $request): string
    {
        if ($url = config('statamic.users.two_factor_setup_url')) {
            return $url;
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
