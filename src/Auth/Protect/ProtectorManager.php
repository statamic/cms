<?php

namespace Statamic\Auth\Protect;

use Statamic\Auth\Protect\Protectors\Authenticated;
use Statamic\Auth\Protect\Protectors\Fallback;
use Statamic\Auth\Protect\Protectors\IpAddress;
use Statamic\Auth\Protect\Protectors\NullProtector;
use Statamic\Auth\Protect\Protectors\Password\PasswordProtector;
use Statamic\Support\Manager;

class ProtectorManager extends Manager
{
    protected function invalidImplementationMessage($name)
    {
        return "Protection scheme [{$name}] is not defined.";
    }

    protected function invalidDriverMessage($driver, $name)
    {
        return "Driver [{$driver}] in protection scheme [{$name}] is invalid.";
    }

    protected function getConfig($name)
    {
        return $this->app['config']["statamic.protect.schemes.$name"];
    }

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

    public function createAuthDriver($config, $name)
    {
        return $this->protector(new Authenticated, $name, $config);
    }

    public function createIpAddressDriver($config, $name)
    {
        return $this->protector(new IpAddress, $name, $config);
    }

    public function createPasswordDriver($config, $name)
    {
        return $this->protector(new PasswordProtector, $name, $config);
    }

    protected function protector($class, $name, $config)
    {
        return $class->setConfig($config)->setScheme($name);
    }

    protected function callCustomCreator(array $config, string $name)
    {
        return $this->protector(
            parent::callCustomCreator($config, $name), $name, $config
        );
    }
}
