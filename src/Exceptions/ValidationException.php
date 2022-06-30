<?php

namespace Statamic\Exceptions;

use Illuminate\Validation\ValidationException as Exception;

class ValidationException extends Exception
{
    public static function summarize($validation)
    {
        return 'The given data was invalid.';
    }
}
