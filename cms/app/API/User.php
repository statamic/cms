<?php

namespace Statamic\API;

use Statamic\Data\Services\UsersService;

class User
{
    /**
     * Get all users
     *
     * @return \Statamic\Data\Users\UserCollection
     */
    public static function all()
    {
        return app(UsersService::class)->all();
    }

    /**
     * Get a user by ID
     *
     * @param string $id
     * @return \Statamic\Contracts\Data\Users\User
     */
    public static function find($id)
    {
        return app(UsersService::class)->id($id);
    }

    /**
     * Get a user by username
     *
     * @param string $username
     * @return \Statamic\Contracts\Data\Users\User
     */
    public static function whereUsername($username)
    {
        return app(UsersService::class)->username($username);
    }

    /**
     * Get a user by email
     *
     * @param string $email
     * @return \Statamic\Contracts\Data\Users\User
     */
    public static function whereEmail($email)
    {
        return app(UsersService::class)->email($email);
    }

    /**
     * Get a user by their oauth provider's id
     *
     * @param string $provider
     * @param string $id
     * @return \Statamic\Contracts\Data\Users\User
     */
    public static function whereOAuth($provider, $id)
    {
        return app(UsersService::class)->oauth($provider, $id);
    }

    /**
     * Create a user
     *
     * @return \Statamic\Contracts\Data\Users\UserFactory
     */
    public static function create()
    {
        return app('Statamic\Contracts\Data\Users\UserFactory');
    }

    /**
     * Get the currently authenticated user
     *
     * @return \Statamic\Contracts\Data\Users\User|\Statamic\Contracts\Permissions\Permissible
     */
    public static function getCurrent()
    {
        return request()->user();
    }

    /**
     * Is the user logged in?
     *
     * @return bool
     */
    public static function loggedIn()
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
    public static function get($id)
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
    public static function username($username)
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
    public static function email($email)
    {
        \Log::notice('User::email() is deprecated. Use User::whereEmail()');

        return self::whereEmail($email);
    }
}
