<?php

namespace Statamic\Exceptions;

class InvalidChallengeModeException extends \Exception
{
    protected $message = 'The challenge mode can only be "code" or "recovery_code".';
}
