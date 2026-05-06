<?php

namespace Statamic\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Statamic\Auth\TwoFactor\EnableTwoFactorAuthentication;
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
            if (empty($user->two_factor_secret)) {
                app(EnableTwoFactorAuthentication::class)($user);
            }

            redirect()->setIntendedUrl($request->fullUrl());

            return redirect($this->redirectUrl());
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

    protected function redirectUrl(): string
    {
        return config('statamic.users.two_factor_setup_url') ?? route($this->redirectRoute());
    }

    protected function redirectRoute(): string
    {
        return 'statamic.two-factor-setup';
    }
}
