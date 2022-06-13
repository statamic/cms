<?php

namespace Statamic\Auth;

use Statamic\Auth\Eloquent\UserRepository as EloquentRepository;
use Statamic\Stache\Repositories\UserRepository as StacheRepository;
use Statamic\Support\Manager;

class UserRepositoryManager extends Manager
{
    public function repository($repository = null)
    {
        return $this->driver($repository);
    }

    public function getDefaultDriver()
    {
        return $this->app['config']['statamic.users.repository'];
    }

    protected function getConfig($name)
    {
        return $this->app['config']["statamic.users.repositories.$name"] ?? null;
    }

    public function createFileDriver(array $config)
    {
        return new StacheRepository($this->app['stache'], $config);
    }

    public function createEloquentDriver(array $config)
    {
        $guard = $this->app['config']['statamic.users.guards.cp'];
        $provider = $this->app['config']["auth.guards.$guard.provider"];
        $config['model'] = $this->app['config']["auth.providers.$provider.model"];

        return new EloquentRepository($config);
    }
}
