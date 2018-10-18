<?php

namespace Statamic;

use InvalidArgumentException;

class Manager
{
    protected $app;
    protected $drivers = [];
    protected $customCreators = [];

    public function __construct($app)
    {
        $this->app = $app;
    }
    
    public function driver($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->drivers[$name] = $this->get($name);
    }

    protected function get($name)
    {
        return $this->stores[$name] ?? $this->resolve($name);
    }
    
    protected function resolve($name)
    {
        if ($name === 'null') {
            return $this->createNullDriver();
        }

        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException($this->invalidImplementationMessage($name));
        }

        if (isset($this->customCreators[$config['driver']])) {
            return $this->callCustomCreator($config);
        } else {
            $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

            if (method_exists($this, $driverMethod)) {
                return $this->{$driverMethod}($config);
            } else {
                throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
            }
        }
    }

    protected function callCustomCreator(array $config)
    {
        return $this->customCreators[$config['driver']]($this->app, $config);
    }

    protected function getConfig($name)
    {
        return $this->app['config']["statamic.static_caching.strategies.{$name}"];
    }

    protected function invalidImplementationMessage($name)
    {
        return "Implementation [{$name}] is not defined.";
    }
}
