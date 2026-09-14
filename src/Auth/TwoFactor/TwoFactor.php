<?php

namespace Statamic\Auth\TwoFactor;

class TwoFactor
{
    public function enabled(): bool
    {
        return config('statamic.users.two_factor_enabled', true);
    }
}
