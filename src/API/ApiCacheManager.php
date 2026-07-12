<?php

namespace Statamic\API;

use InvalidArgumentException;
use Statamic\API\Cachers\DefaultCacher;
use Statamic\API\Cachers\NullCacher;
use Statamic\Support\Manager;

class ApiCacheManager extends Manager
{
    public function getDefaultDriver()
    {
        $config = $this->app['config']['statamic.api.cache'];
        $class = $this->app['config']['statamic.api.cache.class'];

        switch (true) {
            case $config === false:
            case $class === false:
                return false;
            case is_string($class):
                return $class;
            default:
                return DefaultCacher::class;
        }
    }

    public function createNullDriver()
    {
        return new NullCacher;
    }

    public function createClassDriver(string $driverClass, array $config)
    {
        return new $driverClass($config);
    }

    protected function getConfig($name)
    {
        if (! $config = $this->app['config']['statamic.api.cache']) {
            return null;
        }

        return collect($config)
            ->except('class')
            ->all();
    }

    protected function resolve($driver)
    {
        if (! $driver) {
            return $this->createNullDriver();
        }

        $config = $this->getConfig($driver);

        if (is_null($config)) {
            throw new InvalidArgumentException($this->invalidImplementationMessage($driver));
        }

        return $this->createClassDriver($driver, $config);
    }

    protected function invalidImplementationMessage($name)
    {
        return "Api cache config for [{$name}] is not properly defined.";
    }
}
