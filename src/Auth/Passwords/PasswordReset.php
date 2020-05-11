<?php

namespace Statamic\Auth\Passwords;

class PasswordReset
{
    protected static $url;
    protected static $redirect;

    public static function url($token)
    {
        $url = static::$url
            ? sprintf('%s?token=%s', static::$url, $token)
            : route('statamic.password.reset', $token);

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

    public static function redirectAfterReset($redirect)
    {
        static::$redirect = $redirect;
    }
}
