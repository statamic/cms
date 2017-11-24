<?php

namespace Statamic\Data\Services;

use Statamic\Contracts\Data\Users\User;

class UsersService extends BaseService
{
    /**
     * The repo key
     *
     * @var string
     */
    protected $repo = 'users';

    /**
     * Get all users
     *
     * @return \Statamic\Data\Users\UserCollection
     */
    public function all()
    {
        return collect_users(parent::all());
    }

    /**
     * Get a user by their username
     *
     * @param string $username
     * @return User
     */
    public function username($username)
    {
        return $this->all()->first(function ($id, $user) use ($username) {
            return strtolower($user->username()) === strtolower($username);
        });
    }

    /**
     * Get a user by their email
     *
     * @param string $email
     * @return User
     */
    public function email($email)
    {
        return $this->all()->first(function ($id, $user) use ($email) {
            return strtolower($user->email()) === strtolower($email);
        });
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
        return $this->all()->first(function ($user_id, $user) use ($provider, $id) {
            return (string) $user->getOAuthId($provider) === (string) $id;
        });
    }
}