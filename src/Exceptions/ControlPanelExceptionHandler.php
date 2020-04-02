<?php

namespace Statamic\Exceptions;

use Illuminate\Foundation\Exceptions\Handler;
use Throwable;

class ControlPanelExceptionHandler extends Handler
{
    use Concerns\RendersControlPanelExceptions;

    public function render($request, Throwable $e)
    {
        return $this->renderException($request, $e);
    }
}
