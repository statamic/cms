<?php

namespace Statamic\Auth;

use Statamic\Contracts\Auth\User;
use Statamic\Contracts\Auth\UserRepository as RepositoryContract;

abstract class UserRepository implements RepositoryContract
{
    public function create()
    {
        // TODO: Factory?
        throw new \Exception('Factory not supported. Use User::make() to get an instance.');

        return app(UserFactory::class);
    }

    public function current(): ?User
    {
        if (! $user = auth()->user()) {
            return null;
        }

        return $this->fromUser($user);
    }

    public function roleRepository()
    {
        return app($this->roleRepository)->path(
            $this->config['paths']['roles'] ?? resource_path('users/roles.yaml')
        );
    }

    public function userGroupRepository()
    {
        return app($this->userGroupRepository)->path(
            $this->config['paths']['groups'] ?? resource_path('users/groups.yaml')
        );
    }
}
