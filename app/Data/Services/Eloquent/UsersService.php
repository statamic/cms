<?php

namespace Statamic\Data\Services\Eloquent;

use Statamic\Data\Users\Eloquent\User;
use Statamic\Data\Users\Eloquent\OAuth;
use Statamic\Data\Users\Eloquent\Model as UserModel;

class UsersService
{
    /**
     * Get an item by ID
     *
     * @param string $id
     * @return mixed
     */
    public function id($id)
    {
        if ($model = UserModel::find($id)) {
            return $this->makeUser($model);
        }
    }

    /**
     * Get all users
     *
     * @return \Statamic\Data\Users\UserCollection
     */
    public function all()
    {
        $users = UserModel::all()->keyBy('id')->map(function ($model) {
            return $this->makeUser($model);
        });

        return collect_users($users);
    }

    /**
     * Get a user by their username
     *
     * @param string $username
     * @return User
     */
    public function username($username)
    {
        if (! $model = UserModel::where('username', $username)->first()) {
            return null;
        }

        return $this->makeUser($model);
    }

    /**
     * Get a user by their email
     *
     * @param string $email
     * @return User
     */
    public function email($email)
    {
        if (! $model = UserModel::where('email', $email)->first()) {
            return null;
        }

        return $this->makeUser($model);
    }

    /**
     * Get a user by their OAuth provider's ID
     *
     * @param string $provider
     * @param mixed $id
     * @return User
     */
    public function oauth($provider, $id)
    {
        return (new OAuth)->user($provider, $id);
    }

    /**
     * Convert an Eloquent User model to a Statamic User instance.
     *
     * @param  UserModel $model
     * @return User
     */
    private function makeUser(UserModel $model)
    {
        return tap(new User, function ($user) use ($model) {
            $user->model($model);
        });
    }
}
