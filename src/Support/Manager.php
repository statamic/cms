<?php

namespace Statamic\Support;

use Closure;
use InvalidArgumentException;

abstract class Manager
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
        return $this->drivers[$name] ?? $this->resolve($name);
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
            return $this->callCustomCreator($config, $name);
        } else {
            $driverMethod = 'create'.camel_case($config['driver']).'Driver';

            if (method_exists($this, $driverMethod)) {
                return $this->{$driverMethod}($config, $name);
            } else {
                throw new InvalidArgumentException($this->invalidDriverMessage($config['driver'], $name));
            }
        }
    }

    public function extend($driver, Closure $callback)
    {
        $this->customCreators[$driver] = $callback->bindTo($this, $this);

        return $this;
    }

    protected function callCustomCreator(array $config, string $name)
    {
        return $this->customCreators[$config['driver']]($this->app, $config, $name);
    }

    protected function invalidImplementationMessage($name)
    {
        return "Implementation [{$name}] is not defined.";
    }

    protected function invalidDriverMessage($driver, $name)
    {
        return "Driver [{$driver}] in implementation [{$name}] is invalid.";
    }

    abstract public function getDefaultDriver();

    abstract protected function getConfig($name);
}
