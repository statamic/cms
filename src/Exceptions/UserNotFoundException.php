<?php

namespace Statamic\Exceptions;

class UserNotFoundException extends \Exception
{
    protected $user;

    public function __construct($user)
    {
        parent::__construct("User [{$user}] not found");

        $this->user = $user;
    }
}
