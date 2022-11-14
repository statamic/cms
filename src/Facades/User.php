<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Auth\UserRepository;

/**
 * @method static \Statamic\Contracts\Auth\User make()
 * @method static \Statamic\Auth\UserCollection all()
 * @method static null|\Statamic\Contracts\Auth\User find($id)
 * @method static null|\Statamic\Contracts\Auth\User findByEmail(string $email)
 * @method static null|\Statamic\Contracts\Auth\User findByOAuthId(string $provider, string $id)
 * @method static null|\Statamic\Contracts\Auth\User current()
 * @method static null|\Statamic\Contracts\Auth\User fromUser($user)
 * @method static void save(User $user);
 * @method static void delete(User $user);
 * @method static \Statamic\Fields\Blueprint blueprint();
 * @method static \Illuminate\Support\Collection getComputedCallbacks()
 * @method static void computed(string $field, \Closure $callback)
 *
 * @see \Statamic\Contracts\Auth\UserRepository
 */
class User extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UserRepository::class;
    }
}
