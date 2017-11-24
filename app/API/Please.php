<?php

namespace Statamic\API;

class Please
{
    /**
     * Pass any method calls onto the facade.
     *
     * This is essentially an alias for \Please::method()
     *
     * @param string $method
     * @param array $args
     * @return mixed
     */
    public static function __callStatic($method, $args)
    {
        return forward_static_call_array(['Statamic\Console\Please', $method], $args);
    }
}
