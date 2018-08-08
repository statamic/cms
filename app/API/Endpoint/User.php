<?php

namespace Statamic\API\Endpoint;

use Statamic\Contracts\Data\Repositories\UserRepository;

class User
{
    /**
     * Get all users
     *
     * @return \Statamic\Data\Users\UserCollection
     */
    public function all()
    {
        return $this->repo()->all();
    }

    /**
     * Get a user by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Data\Users\User
     */
    public function find($id)
    {
        return $this->repo()->find($id);
    }

    /**
     * Get a user by username
     *
     * @param string $username
     * @return \Statamic\Contracts\Data\Users\User
     */
    public function whereUsername($username)
    {
        return $this->repo()->username($username);
    }

    /**
     * Get a user by email
     *
     * @param string $email
     * @return \Statamic\Contracts\Data\Users\User
     */
    public function whereEmail($email)
    {
        return $this->repo()->email($email);
    }

    /**
     * Get a user by their oauth provider's id
     *
     * @param string $provider
     * @param string $id
     * @return \Statamic\Contracts\Data\Users\User
     */
    public function whereOAuth($provider, $id)
    {
        return $this->repo()->oauth($provider, $id);
    }

    /**
     * Create a user
     *
     * @return \Statamic\Contracts\Data\Users\UserFactory
     */
    public function create()
    {
        return app('Statamic\Contracts\Data\Users\UserFactory');
    }

    /**
     * Get the currently authenticated user
     *
     * @return \Statamic\Contracts\Data\Users\User|\Statamic\Contracts\Permissions\Permissible
     */
    public function getCurrent()
    {
        return request()->user();
    }

    /**
     * Is the user logged in?
     *
     * @return bool
     */
    public function loggedIn()
    {
        return (bool) self::getCurrent();
    }

    /**
     * Get a user by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Data\Users\User
     * @deprecated since 2.1
     */
    public function get($id)
    {
        \Log::notice('User::get() is deprecated. Use User::find()');

        return self::find($id);
    }

    /**
     * Get a user by username
     *
     * @param string $username
     * @return \Statamic\Contracts\Data\Users\User
     * @deprecated since 2.1
     */
    public function username($username)
    {
        \Log::notice('User::username() is deprecated. Use User::whereUsername()');

        return self::whereUsername($username);
    }

    /**
     * Get a user by email
     *
     * @param string $email
     * @return \Statamic\Contracts\Data\Users\User
     */
    public function email($email)
    {
        \Log::notice('User::email() is deprecated. Use User::whereEmail()');

        return self::whereEmail($email);
    }

    protected function repo()
    {
        return app(UserRepository::class);
    }
}
