<?php

namespace Statamic\Exceptions\Concerns;

use Illuminate\Validation\ValidationException;
use Statamic\Exceptions\NotFoundHttpException;
use Statamic\Exceptions\StatamicProAuthorizationException;
use Statamic\Exceptions\StatamicProRequiredException;

trait RendersApiExceptions
{
    protected function renderException($request, $e)
    {
        if ($e instanceof ValidationException) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json(['message' => $e->getMessage() ?: 'Not found.'], 404);
        }

        if ($e instanceof StatamicProAuthorizationException) {
            throw new StatamicProRequiredException($e->getMessage());
        }

        return parent::render($request, $e);
    }
}
