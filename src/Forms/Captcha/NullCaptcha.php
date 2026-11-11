<?php

namespace Statamic\Forms\Captcha;

use Statamic\Contracts\Forms\Captcha;

class NullCaptcha implements Captcha
{
    public function widget(): string
    {
        return '';
    }

    public function verify(?string $token, ?string $ip = null): bool
    {
        return true;
    }

    public function field(): string
    {
        return 'captcha';
    }
}
