<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Handle implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^[a-z]+(?:_{0,1}[a-z])*$/', $value)) {
            $fail('statamic::validation.handle')->translate();
        }
    }
}
