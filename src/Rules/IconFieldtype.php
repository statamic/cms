<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class IconFieldtype implements ValidationRule
{
    public function __construct(private $fieldtype)
    {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value !== strip_tags($value)) {
            $fail("The {$attribute} field must be a valid icon name.");
        }
    }
}