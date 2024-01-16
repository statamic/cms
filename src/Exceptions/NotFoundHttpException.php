<?php

namespace Statamic\Exceptions;

use Statamic\Exceptions\Concerns\RendersHttpExceptions;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException as SymfonyException;

class NotFoundHttpException extends SymfonyException
{
    use RendersHttpExceptions;

    public function getApiMessage()
    {
        return 'Not found.';
    }
}
