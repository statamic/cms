<?php

namespace Statamic\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler;

class ApiExceptionHandlerForLaravelSix extends Handler
{
    public function render($request, Exception $e)
    {
        return app(ApiExceptionHandler::class)->render($request, $e);
    }
}
