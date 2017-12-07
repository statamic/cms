<?php

namespace Statamic\API\Endpoint;

class Crypt
{
    /**
     * Get the Encrypter
     *
     * @return \Illuminate\Encryption\Encrypter
     */
    private function encrypter()
    {
        return app('encrypter');
    }

    /**
     * Encrypt a string
     *
     * @param mixed $value
     * @return string
     */
    public function encrypt($value)
    {
        return self::encrypter()->encrypt($value);
    }

    /**
     * Decrypt a string
     *
     * @param $string
     * @return string
     */
    public function decrypt($string)
    {
        return self::encrypter()->decrypt($string);
    }
}
