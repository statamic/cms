<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Handle implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^[a-z][a-z0-9]*(?:_{0,1}[a-z0-9])*$/', $value)) {
            $fail('statamic::validation.handle')->translate();
        }
    }
}
