<?php

namespace Statamic\Http\Middleware\API;

use Statamic\Exceptions\ApiExceptionHandler;
use Statamic\Exceptions\ApiExceptionHandlerForLaravelSix;
use Statamic\Http\Middleware\SwapExceptionHandler as Middleware;

class SwapExceptionHandler extends Middleware
{
    public function handler()
    {
        return version_compare(app()->version(), 7, '>=')
            ? ApiExceptionHandler::class
            : ApiExceptionHandlerForLaravelSix::class;
    }
}
