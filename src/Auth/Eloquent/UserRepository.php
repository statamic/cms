<?php

namespace Statamic\Auth\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Statamic\Auth\UserCollection;
use Statamic\Auth\UserRepository as BaseRepository;
use Statamic\Contracts\Auth\User as UserContract;
use Statamic\Facades\Blink;

class UserRepository extends BaseRepository
{
    protected $config;
    protected $roleRepository = RoleRepository::class;
    protected $userGroupRepository = UserGroupRepository::class;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function make(): UserContract
    {
        return (new User)->model(new $this->config['model']);
    }

    public function all(): UserCollection
    {
        $users = $this->model('all')->keyBy('id')->map(function ($model) {
            return $this->makeUser($model);
        });

        return UserCollection::make($users);
    }

    public function find($id): ?UserContract
    {
        return Blink::once("eloquent-user-find-{$id}", function () use ($id) {
            if ($model = $this->model('find', $id)) {
                return $this->makeUser($model);
            }

            return null;
        });
    }

    public function findByEmail(string $email): ?UserContract
    {
        if (! $model = $this->model('where', 'email', $email)->first()) {
            return null;
        }

        return $this->makeUser($model);
    }

    public function model($method, ...$args)
    {
        $model = $this->config['model'];

        return call_user_func_array([$model, $method], $args);
    }

    /**
     * Convert an Eloquent User model to a Statamic User instance.
     *
     * @param  Model $model
     * @return User
     */
    private function makeUser(Model $model)
    {
        return User::fromModel($model);
    }

    public function query()
    {
        return new UserQueryBuilder($this->model('query'));
    }

    public function save(UserContract $user)
    {
        $user->saveToDatabase();

        Blink::forget("eloquent-user-find-{$user->id()}");
    }

    public function delete(UserContract $user)
    {
        $user->model()->delete();

        Blink::forget("eloquent-user-find-{$user->id()}");
    }

    public function fromUser($user): ?UserContract
    {
        if ($user instanceof UserContract) {
            return $user;
        }

        if (method_exists($user, 'toStatamicUser')) {
            return $user->toStatamicUser();
        }

        if ($user instanceof Model) {
            return User::fromModel($user);
        }

        return null;
    }
}
