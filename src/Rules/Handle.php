<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Str;

class Handle implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Str::startsWith($value, range(0, 9))) {
            $fail('statamic::validation.handle_starts_with_number')->translate();

            return;
        }

        if (! preg_match('/^[a-zA-Z][a-zA-Z0-9]*(?:_{0,1}[a-zA-Z0-9])*$/', $value)) {
            $fail('statamic::validation.handle')->translate();
        }
    }
}
