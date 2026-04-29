<?php

namespace Statamic\Http\Middleware\CP;

use Illuminate\Http\Request;
use Statamic\Http\Middleware\RedirectIfTwoFactorSetupIncomplete as Middleware;

class RedirectIfTwoFactorSetupIncomplete extends Middleware
{
    protected function redirectRoute(): string
    {
        return 'statamic.cp.two-factor-setup';
    }

    protected function redirectUrl(Request $request): string
    {
        return route($this->redirectRoute(), [
            'referer' => $request->fullUrl(),
        ]);
    }
}
