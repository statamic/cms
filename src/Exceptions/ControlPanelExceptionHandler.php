<?php

namespace Statamic\Exceptions;

use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Throwable;

class ControlPanelExceptionHandler extends Handler
{
    use Concerns\RendersControlPanelExceptions;

    public function render($request, Throwable $e)
    {
        return $this->renderException($request, $e);
    }

    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $response = parent::convertValidationExceptionToResponse($e, $request);

        if ($response instanceof JsonResponse) {
            $original = $response->getOriginalContent();
            $original['message'] = __($original['message']);
            $response->setContent(json_encode($original));
        }

        return $response;
    }
}
