<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Statamic\Contracts\Data\Users\User;
use Statamic\Data\Users\UserCollection;
use Statamic\Contracts\Data\Repositories\UserRepository as RepositoryContract;

class UserRepository implements RepositoryContract
{
    protected $store;

    public function __construct(Stache $stache)
    {
        $this->store = $stache->store('users');
    }

    public function all(): UserCollection
    {
        return collect_users($this->store->getItems());
    }

    public function find($id): ?User
    {
        return $this->store->getItem($id);
    }

    public function username($username)
    {
        // TODO: TDD
        return $this->store->getItems()->first(function ($user) use ($username) {
            return $user->username() === $username;
        });
    }
}
