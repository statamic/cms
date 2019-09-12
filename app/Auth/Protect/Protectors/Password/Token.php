<?php

namespace Statamic\Auth\Protect\Protectors\Password;

use Statamic\Facades\Str;

class Token
{
    public function generate()
    {
        return Str::random(32);
    }
}
