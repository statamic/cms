<?php

namespace Statamic\Exceptions;

class TwoFactorNotSetupException extends \Exception
{
    protected $message = 'Two Factor is not set up for this user. They are missing a two factor secret.';
}
