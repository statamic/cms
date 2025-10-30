<?php

namespace Statamic\Exceptions;

class CascadeDataNotFoundException extends \Exception
{
    protected $key;

    public function __construct($key)
    {
        parent::__construct("Cascade data [{$key}] not found");

        $this->key = $key;
    }
}
