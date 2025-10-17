<?php

namespace Statamic\Exceptions\Concerns;

use Illuminate\Auth\Access\AuthorizationException as IlluminateAuthException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Statamic\Statamic;

trait RendersControlPanelExceptions
{
    protected function renderException($request, $e)
    {
        if ($e instanceof IlluminateAuthException && ! $request->expectsJson()) {
            return redirect($this->getAuthExceptionRedirectUrl())->withError($e->getMessage());
        }

        $response = parent::render($request, $e);

        if ($this->shouldRenderErrorPage($response)) {
            Statamic::$isRenderingCpException = true;

            return Inertia::render('errors/Error', ['status' => $response->getStatusCode()])
                ->toResponse($request)
                ->setStatusCode($response->getStatusCode());
        }

        return $response;
    }

    protected function getAuthExceptionRedirectUrl()
    {
        $referrer = request()->header('referer');

        // If we came to this URL from another, we'll send them back, but not
        // if it was the login page otherwise there'd be a redirect loop.
        if ($referrer && $referrer != cp_route('login')) {
            return $referrer;
        }

        // If we can't send them back because they hit this page directly,
        // we'll attempt to redirect them to the Control Panel index.
        $target = cp_route('index');

        // If we're already there, there'd be a redirect loop, so we'll
        // send them to a page that tells them they're unauthorized.
        if (request()->getUri() === $target) {
            return cp_route('unauthorized');
        }

        return $target;
    }

    protected function shouldRenderErrorPage($response): bool
    {
        if (
            $response instanceof JsonResponse
            || $response instanceof RedirectResponse
        ) {
            return false;
        }

        return app()->isProduction();
    }
}
