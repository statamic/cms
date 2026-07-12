<?php

namespace Statamic\Exceptions;

class GlobalSetNotFoundException extends \Exception
{
    protected $global;

    public function __construct($global)
    {
        parent::__construct("Global Set [{$global}] not found");

        $this->global = $global;
    }
}
