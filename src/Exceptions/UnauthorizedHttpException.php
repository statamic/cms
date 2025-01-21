<?php

namespace Statamic\Exceptions;

use Statamic\Exceptions\Concerns\RendersHttpExceptions;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UnauthorizedHttpException extends HttpException
{
    use RendersHttpExceptions;
}
