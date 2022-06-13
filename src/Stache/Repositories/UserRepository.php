<?php

namespace Statamic\Stache\Repositories;

use Statamic\Auth\File\RoleRepository;
use Statamic\Auth\File\User as FileUser;
use Statamic\Auth\File\UserGroupRepository;
use Statamic\Auth\UserCollection;
use Statamic\Auth\UserRepository as BaseRepository;
use Statamic\Contracts\Auth\User;
use Statamic\Stache\Query\UserQueryBuilder;
use Statamic\Stache\Stache;

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

    public function all(): UserCollection
    {
        return $this->query()->get();
    }

    public function find($id): ?User
    {
        return $this->store->getItem($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->query()->where('email', $email)->first();
    }

    public function query()
    {
        return new UserQueryBuilder($this->store);
    }

    public function save(User $user)
    {
        if (! $user->id()) {
            $user->id($this->stache->generateId());
        }

        $this->store->save($user);
    }

    public function delete(User $user)
    {
        $this->store->delete($user);
    }

    public function fromUser($user): ?User
    {
        if ($user instanceof User) {
            return $user;
        }

        return null;
    }

    public static function bindings(): array
    {
        return [
            User::class => FileUser::class,
        ];
    }
}
