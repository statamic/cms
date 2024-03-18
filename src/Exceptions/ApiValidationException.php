<?php

namespace Statamic\Exceptions;

use Illuminate\Validation\ValidationException;

class ApiValidationException extends ValidationException
{
    public function render()
    {
        return response()->json(['message' => $this->getMessage()], 422);
    }
}
