<?php

namespace Statamic\Auth;

use Statamic\Manager;
use Statamic\Eloquent\Auth\UserRepository as EloquentRepository;
use Statamic\Stache\Repositories\UserRepository as StacheRepository;

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
        return new EloquentRepository($config);
    }
}
