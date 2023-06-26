<?php

namespace Statamic\Http\Middleware\API;

use Statamic\Exceptions\ApiExceptionHandler;
use Statamic\Http\Middleware\SwapExceptionHandler as Middleware;

class SwapExceptionHandler extends Middleware
{
    public function handler()
    {
        return ApiExceptionHandler::class;
    }
}
