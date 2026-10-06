<?php

namespace Statamic\Exceptions;

use Exception;

class FrontendAuthenticationDisabledException extends Exception
{
    public function __construct()
    {
        parent::__construct('Front-end authentication is disabled. Enable it using the statamic.users.frontend_auth_enabled config option.');
    }
}
