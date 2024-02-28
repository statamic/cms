<?php

namespace Statamic\Auth\Protect\Protectors;

use Statamic\Exceptions\ForbiddenHttpException;

class Fallback extends Protector
{
    public function protect()
    {
        throw new ForbiddenHttpException();
    }
}
