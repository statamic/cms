<?php

namespace Statamic\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler;

class ControlPanelExceptionHandlerForLaravelSix extends Handler
{
    public function render($request, Exception $e)
    {
        return app(ControlPanelExceptionHandler::class)->render($request, $e);
    }
}
