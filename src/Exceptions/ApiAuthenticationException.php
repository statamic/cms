<?php

namespace Statamic\Exceptions;

use Illuminate\Auth\AuthenticationException as Exception;
use Illuminate\Contracts\Support\Responsable;

class ApiAuthenticationException extends Exception implements Responsable
{
    public function toResponse($request)
    {
        return response()->json(
            ['message' => $this->getMessage()],
            401,
            ['WWW-Authenticate' => 'Bearer'],
        );
    }
}
