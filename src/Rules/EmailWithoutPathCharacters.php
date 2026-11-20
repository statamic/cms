<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class EmailWithoutPathCharacters implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_string($value) && preg_match('/[\/\\\\\0]/', $value)) {
            $fail('validation.email')->translate();
        }
    }
}
