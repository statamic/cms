<?php

namespace Statamic\Auth\Passwords;

class PasswordReset
{
    const BROKER_RESETS = 'resets';
    const BROKER_ACTIVATIONS = 'activations';

    protected static $url;
    protected static $route;
    protected static $redirect;

    public static function url($token, $broker)
    {
        $route = $broker === self::BROKER_ACTIVATIONS ? 'statamic.account.activate' : 'statamic.password.reset';

        if (static::$route) {
            $route = static::$route;
        }

        $defaultUrl = route($route, $token);

        $url = static::$url
            ? sprintf('%s?token=%s', static::$url, $token)
            : $defaultUrl;

        parse_str(parse_url($url, PHP_URL_QUERY) ?: '', $query);

        if (static::$redirect) {
            $query = array_merge($query, ['redirect' => static::$redirect]);
        }

        return explode('?', $url)[0].'?'.http_build_query($query);

        return $url;
    }

    public static function resetFormUrl($url)
    {
        static::$url = $url;
    }

    public static function resetFormRoute($route)
    {
        static::$route = $route;
    }

    public static function redirectAfterReset($redirect)
    {
        static::$redirect = $redirect;
    }
}
