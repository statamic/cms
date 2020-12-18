<?php

namespace Statamic\Console;

class NullConsole
{
    /**
     * Any calls to this class just return itself so you can chain forever and ever.
     *
     * @param string $method
     * @param array $args
     * @return $this
     */
    public function __call($method, $args)
    {
        return $this;
    }
}
