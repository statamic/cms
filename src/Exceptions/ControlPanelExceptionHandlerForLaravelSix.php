<?php

namespace Statamic\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler;

class ControlPanelExceptionHandlerForLaravelSix extends Handler
{
    use Concerns\RendersControlPanelExceptions;

    public function render($request, Exception $e)
    {
        return $this->renderException($request, $e);
    }
}
