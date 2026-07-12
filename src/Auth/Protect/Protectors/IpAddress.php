<?php

namespace Statamic\Auth\Protect\Protectors;

use Statamic\Exceptions\ForbiddenHttpException;
use Statamic\Support\Arr;

class IpAddress extends Protector
{
    public function protect()
    {
        $allowed = Arr::get($this->config, 'allowed', []);

        $requestIps = request()->ips();

        if (empty(array_intersect($requestIps, $allowed))) {
            throw new ForbiddenHttpException();
        }
    }
}
