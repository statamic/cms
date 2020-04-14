<?php

namespace Statamic\Exceptions\Concerns;

use Statamic\Exceptions\NotFoundHttpException;

trait RendersApiExceptions
{
    protected function renderException($request, $e)
    {
        if ($e instanceof NotFoundHttpException) {
            return response()->json(['message' => $e->getMessage() ?: 'Not found.'], 404);
        }

        return parent::render($request, $e);
    }
}
