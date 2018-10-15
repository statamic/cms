<?php

namespace Statamic\Auth\Protect\Protectors;

class IpAddress extends Protector
{
    public function protect()
    {
        $ips = array_get($this->config, 'allowed', []);

        if (! in_array(request()->ip(), $ips)) {
            abort(403);
        }
    }
}
