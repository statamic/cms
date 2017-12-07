<?php

namespace Statamic\API\Endpoint;

class Hash
{
    /**
     * @return \Illuminate\Contracts\Hashing\Hasher
     */
    private function hasher()
    {
        return app('hash');
    }

    /**
     * Hash a value
     *
     * @param string $value
     * @return string
     */
    public function make($value)
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
    public function check($value, $hash)
    {
        return self::hasher()->check($value, $hash);
    }
}
