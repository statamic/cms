<?php

namespace Statamic\Auth\Protect\Protectors;

use Statamic\Exceptions\ForbiddenHttpException;

class IpAddress extends Protector
{
    public function protect()
    {
        $ips = array_get($this->config, 'allowed', []);

        if (! in_array(request()->ip(), $ips)) {
            throw new ForbiddenHttpException();
        }
    }
}
