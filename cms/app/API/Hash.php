<?php

namespace Statamic\API;

class Hash
{
    /**
     * @return \Illuminate\Contracts\Hashing\Hasher
     */
    private static function hasher()
    {
        return app('hash');
    }

    /**
     * Hash a value
     *
     * @param string $value
     * @return string
     */
    public static function make($value)
    {
        return self::hasher()->make($value);
    }

    /**
     * Check the given plain value against a hash.
     *
     * @param string $value
     * @param string $hash
     * @return bool
     */
    public static function check($value, $hash)
    {
        return self::hasher()->check($value, $hash);
    }
}
