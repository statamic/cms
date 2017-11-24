<?php

namespace Statamic\API;

use Illuminate\Contracts\Auth\Authenticatable;

class Auth
{
    /**
     * Log a user in
     *
     * @param  string|Authenticatable $username Either a User object, or a username
     * @param  string|null $password The user's password, or null if using a User object
     * @param  bool $remember Whether to remember the user
     * @return bool
     */
    public static function login($username, $password = null, $remember = false)
    {
        if ($username instanceof Authenticatable) {
            return \Auth::login($username, $remember);
        }

        $credentials = compact('username', 'password');

        return \Auth::attempt($credentials, $remember);
    }
}
