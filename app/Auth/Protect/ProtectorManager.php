<?php

namespace Statamic\Auth\Protect;

use Illuminate\Support\Manager;
use Statamic\Auth\Protect\Protectors\Fallback;
use Statamic\Auth\Protect\Protectors\IpAddress;
use Statamic\Auth\Protect\Protectors\Authenticated;
use Statamic\Auth\Protect\Protectors\NullProtector;
use Statamic\Auth\Protect\Protectors\Password\PasswordProtector;

class ProtectorManager extends Manager
{
    public function getDefaultDriver()
    {
        return 'fallback';
    }

    public function createNullDriver()
    {
        return new NullProtector;
    }

    public function createFallbackDriver()
    {
        return new Fallback;
    }

    public function createAuthDriver()
    {
        return new Authenticated;
    }

    public function createIpAddressDriver()
    {
        return new IpAddress;
    }

    public function createPasswordDriver()
    {
        return new PasswordProtector;
    }
}
