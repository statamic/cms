<?php

namespace Statamic\Exceptions;

use Exception;

class OAuthEmailExistsException extends Exception
{
    public function __construct(public readonly ?string $email = null)
    {
        parent::__construct("A user already exists with the email [{$email}].");
    }
}
