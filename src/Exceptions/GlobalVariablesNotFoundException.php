<?php

namespace Statamic\Exceptions;

class GlobalVariablesNotFoundException extends \Exception
{
    protected $variables;

    public function __construct($variables)
    {
        parent::__construct("Global Variables [{$variables}] not found");

        $this->variables = $variables;
    }
}
