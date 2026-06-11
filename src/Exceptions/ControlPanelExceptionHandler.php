<?php

namespace Statamic\Exceptions;

use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Statamic\Exceptions\ValidationException as StatamicValidationException;
use Throwable;

use function Statamic\trans as __;

class ControlPanelExceptionHandler extends Handler
{
    use Concerns\RendersControlPanelExceptions;

    public function render($request, Throwable $e)
    {
        return $this->renderException($request, $e);
    }

    protected function shouldReturnJson($request, Throwable $e)
    {
        // The Control Panel relies on deterministic JSON (e.g. 422 validation)
        // responses for its XHR requests. An app may register a custom
        // shouldRenderJsonWhen() callback that Laravel applies to every handler,
        // which would override this, so we always honor the request itself.
        return $request->expectsJson();
    }

    protected function convertValidationExceptionToResponse(ValidationException $e, $request)
    {
        $e = $this->newStatamicValidationException($e);

        $response = parent::convertValidationExceptionToResponse($e, $request);

        if ($response instanceof JsonResponse) {
            $original = $response->getOriginalContent();
            $original['message'] = __($original['message']);
            $response->setContent(json_encode($original));
        }

        return $response;
    }

    private function newStatamicValidationException(ValidationException $old): StatamicValidationException
    {
        $e = new StatamicValidationException($old->validator, $old->response, $old->errorBag);

        $e->redirectTo($old->redirectTo);
        $e->status($old->status);

        return $e;
    }
}
