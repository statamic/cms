<?php

namespace Statamic\Stache\Repositories;

use Statamic\Stache\Stache;
use Statamic\OAuth\Provider;
use Statamic\Auth\UserFactory;
use Statamic\Auth\UserCollection;
use Statamic\Contracts\Auth\User;
use Statamic\Auth\File\RoleRepository;
use Statamic\Auth\File\UserQueryBuilder;
use Statamic\Auth\File\User as FileUser;
use Statamic\Auth\File\UserGroupRepository;
use Statamic\Auth\UserRepository as BaseRepository;

class UserRepository extends BaseRepository
{
    protected $stache;
    protected $store;
    protected $config;
    protected $roleRepository = RoleRepository::class;
    protected $userGroupRepository = UserGroupRepository::class;

    public function __construct(Stache $stache, array $config = [])
    {
        $this->stache = $stache;
        $this->store = $stache->store('users');
        $this->config = $config;
    }

    public function make(): User
    {
        return new FileUser;
    }

    public function all(): UserCollection
    {
        return collect_users($this->store->getItems());
    }

    public function find($id): ?User
    {
        return $this->store->getItem($id);
    }

    public function findByEmail(string $email): ?User
    {
        // TODO: TDD
        return $this->store->getItems()->first(function ($user) use ($email) {
            return $user->email() === $email;
        });
    }

    public function current(): ?User
    {
        return request()->user();
    }

    public function query()
    {
        return new UserQueryBuilder;
    }

    public function save(User $user)
    {
        if (! $user->id()) {
            $user->id($this->stache->generateId());
        }

        $this->store->insert($user);

        $this->store->save($user);
    }

    public function delete(User $user)
    {
        $this->store->remove($user);

        $this->store->delete($user);
    }

    public function findByOAuthId(string $provider, string $id): ?User
    {
        return $this->find(
            (new Provider($provider))->getUserId($id)
        );
    }
}
