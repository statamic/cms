<?php

namespace Statamic\Exceptions;

use LogicException;

class UndefinedDictionaryException extends LogicException
{
    public function __construct()
    {
        parent::__construct('A dictionary has not been configured');
    }
}
