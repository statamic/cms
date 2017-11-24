<?php

namespace Statamic\API;

class Crypt
{
    /**
     * Get the Encrypter
     *
     * @return \Illuminate\Encryption\Encrypter
     */
    private static function encrypter()
    {
        return app('encrypter');
    }

    /**
     * Encrypt a string
     *
     * @param mixed $value
     * @return string
     */
    public static function encrypt($value)
    {
        return self::encrypter()->encrypt($value);
    }

    /**
     * Decrypt a string
     *
     * @param $string
     * @return string
     */
    public static function decrypt($string)
    {
        return self::encrypter()->decrypt($string);
    }
}
