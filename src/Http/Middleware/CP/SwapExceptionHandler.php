<?php

namespace Statamic\Http\Middleware\CP;

use Statamic\Exceptions\ControlPanelExceptionHandler;
use Statamic\Http\Middleware\SwapExceptionHandler as Middleware;

class SwapExceptionHandler extends Middleware
{
    public function handler()
    {
        return ControlPanelExceptionHandler::class;
    }
}
