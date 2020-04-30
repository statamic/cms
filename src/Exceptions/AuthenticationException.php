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
            : redirect()->route('statamic.cp.login');
    }
}
