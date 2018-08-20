<?php

namespace Statamic\Exceptions;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Auth\Access\AuthorizationException as Exception;

class AuthorizationException extends Exception implements Responsable
{
    public function toResponse($request)
    {
        return back_or_route('statamic.cp.index')->withErrors($this->getMessage());
    }
}
