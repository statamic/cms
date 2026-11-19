<?php

namespace Statamic\Exceptions;

use Exception;

class OAuthEmailExistsException extends Exception
{
    public function __construct()
    {
        parent::__construct('A user already exists with the OAuth email.');
    }
}
