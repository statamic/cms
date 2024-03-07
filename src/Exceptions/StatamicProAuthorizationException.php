<?php

namespace Statamic\Exceptions;

class StatamicProAuthorizationException extends AuthorizationException
{
    public function render()
    {
        throw new StatamicProRequiredException($this->getMessage());
    }
}
