<?php

namespace Statamic\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler;

class ApiExceptionHandlerForLaravelSix extends Handler
{
    use Concerns\RendersApiExceptions;

    public function render($request, Exception $e)
    {
        return $this->renderException($request, $e);
    }
}
