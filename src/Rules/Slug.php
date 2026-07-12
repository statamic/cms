<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class Slug implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match('/^[a-zA-Z0-9]+(?:[-_]{0,1}[a-zA-Z0-9])*$/', $value)) {
            $fail('statamic::validation.slug')->translate();
        }
    }
}
