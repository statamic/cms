<?php

namespace Statamic\Facades;

use Illuminate\Support\Facades\Facade;
use Statamic\Contracts\Auth\UserRepository;
use Statamic\OAuth\Provider;

/**
 * @method static \Statamic\Contracts\Auth\User make()
 * @method static \Statamic\Auth\UserCollection all()
 * @method static null|\Statamic\Contracts\Auth\User find($id)
 * @method static null|\Statamic\Contracts\Auth\User findByEmail(string $email)
 * @method static null|\Statamic\Contracts\Auth\User findByOAuthId(Provider $provider, string $id)
 * @method static \Statamic\Contracts\Auth\User findOrFail($id)
 * @method static query()
 * @method static int count()
 * @method static null|\Statamic\Contracts\Auth\User current()
 * @method static null|\Statamic\Contracts\Auth\User fromUser($user)
 * @method static void save(\Statamic\Contracts\Auth\User $user);
 * @method static void delete(\Statamic\Contracts\Auth\User $user);
 * @method static \Statamic\Fields\Blueprint blueprint();
 * @method static \Illuminate\Support\Collection getComputedCallbacks()
 * @method static void computed(string|array $field, ?\Closure $callback = null)
 *
 * @see \Statamic\Contracts\Auth\UserRepository
 * @see \Statamic\Auth\User
 */
class User extends Facade
{
    protected static function getFacadeAccessor()
    {
        return UserRepository::class;
    }
}
