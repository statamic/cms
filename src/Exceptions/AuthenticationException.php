<?php

namespace Statamic\Exceptions;

use Illuminate\Auth\AuthenticationException as Exception;
use Illuminate\Contracts\Support\Responsable;

class AuthenticationException extends Exception implements Responsable
{
    public function toResponse($request)
    {
        return $request->expectsJson()
            ? response()->json(['message' => $this->getMessage()], 401)
            : $this->handleRedirect();
    }

    protected function handleRedirect()
    {
        if (! config('statamic.cp.auth.enabled', true)) {
            return config('statamic.cp.auth.redirect_to')
                ? redirect()->guest(config('statamic.cp.auth.redirect_to'))
                : abort(401);
        }

        return redirect()->route('statamic.cp.login');
    }
}
