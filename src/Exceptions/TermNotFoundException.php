<?php

namespace Statamic\Exceptions;

use Exception;

class TermNotFoundException extends Exception
{
    protected $term;

    public function __construct($term)
    {
        parent::__construct("Term [{$term}] not found");

        $this->term = $term;
    }
}
