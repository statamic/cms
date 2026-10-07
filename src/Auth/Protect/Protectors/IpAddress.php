<?php

namespace Statamic\Auth\Protect\Protectors;

use Statamic\Exceptions\ForbiddenHttpException;
use Statamic\Support\Arr;

class IpAddress extends Protector
{
    public function protect()
    {
        $allowed = Arr::get($this->config, 'allowed', []);

        if (! in_array(request()->ip(), $allowed, strict: true)) {
            throw new ForbiddenHttpException();
        }
    }
}
