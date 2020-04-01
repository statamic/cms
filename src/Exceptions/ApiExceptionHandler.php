<?php

namespace Statamic\Exceptions;

use Exception;
use Illuminate\Foundation\Exceptions\Handler;
use Statamic\Exceptions\NotFoundHttpException;

class ApiExceptionHandler extends Handler
{
    public function render($request, Exception $e)
    {
        if ($e instanceof NotFoundHttpException) {
            return response()->json(['message' => $e->getMessage() ?: 'Not found.'], 404);
        }

        return parent::render($request, $e);
    }
}
