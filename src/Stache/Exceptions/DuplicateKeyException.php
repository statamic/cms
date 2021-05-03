<?php

namespace Statamic\Stache\Exceptions;

use Exception;

class DuplicateKeyException extends Exception
{
    protected $key;
    protected $path;

    public function __construct($key, $path)
    {
        $this->key = $key;
        $this->path = $path;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getPath()
    {
        return $this->path;
    }
}
