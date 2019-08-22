<?php

namespace Statamic\Auth;

use Statamic\Contracts\Auth\User;
use Statamic\Contracts\Auth\AuthenticatesWithStatamic;
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
        if (! $user = request()->user()) {
            return null;
        }

        if ($user instanceof User) {
            return $user;
        }

        if ($user instanceof AuthenticatesWithStatamic) {
            return $user->statamicUser();
        }

        return null;
    }

    public function roleRepository()
    {
        return app($this->roleRepository)->path(
            $this->config['paths']['roles'] ?? config_path('statamic/user_roles.yaml')
        );
    }

    public function userGroupRepository()
    {
        return app($this->userGroupRepository)->path(
            $this->config['paths']['groups'] ?? config_path('statamic/user_groups.yaml')
        );
    }
}
