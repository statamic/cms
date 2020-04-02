<?php

namespace Statamic\Exceptions;

use Illuminate\Foundation\Exceptions\Handler;
use Throwable;

class ApiExceptionHandler extends Handler
{
    use Concerns\RendersApiExceptions;

    public function render($request, Throwable $e)
    {
        return $this->renderException($request, $e);
    }
}
