<?php

namespace Statamic\Exceptions;

class ItemNotFoundException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Item not found');
    }
}
