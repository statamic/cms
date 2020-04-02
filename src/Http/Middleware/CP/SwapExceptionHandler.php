<?php

namespace Statamic\Http\Middleware\CP;

use Statamic\Exceptions\ControlPanelExceptionHandler;
use Statamic\Exceptions\ControlPanelExceptionHandlerForLaravelSix;
use Statamic\Http\Middleware\SwapExceptionHandler as Middleware;

class SwapExceptionHandler extends Middleware
{
    public function handler()
    {
        return version_compare(app()->version(), 7, '>=')
            ? ControlPanelExceptionHandler::class
            : ControlPanelExceptionHandlerForLaravelSix::class;
    }
}
