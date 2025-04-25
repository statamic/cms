<?php

namespace Statamic\Query\Exceptions;

class RecordNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Record not found');
    }
}
