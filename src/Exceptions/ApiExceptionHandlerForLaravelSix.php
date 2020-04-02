<?php

namespace Statamic\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler;

class ApiExceptionHandlerForLaravelSix extends Handler
{
    public function render($request, Exception $e)
    {
        app(static::class)->render($request, $e);
    }
}
