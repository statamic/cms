<?php

namespace Statamic\Exceptions;

use Illuminate\Foundation\Exceptions\Handler;
use Statamic\Exceptions\NotFoundHttpException;
use Throwable;

class ApiExceptionHandler extends Handler
{
    public function render($request, Throwable $e)
    {
        if ($e instanceof NotFoundHttpException) {
            return response()->json(['message' => $e->getMessage() ?: 'Not found.'], 404);
        }

        return parent::render($request, $e);
    }
}
