<?php

namespace Statamic\Http\Middleware\CP;

use Statamic\Http\Middleware\RedirectIfTwoFactorSetupIncomplete as Middleware;

class RedirectIfTwoFactorSetupIncomplete extends Middleware
{
    protected function redirectRoute(): string
    {
        return 'statamic.cp.two-factor-setup';
    }
}
