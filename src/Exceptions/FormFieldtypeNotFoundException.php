<?php

namespace Statamic\Exceptions;

use Exception;

class FormFieldtypeNotFoundException extends Exception
{
    public function __construct($fieldtype)
    {
        parent::__construct("Form Fieldtype [{$fieldtype}] not found");
    }
}
