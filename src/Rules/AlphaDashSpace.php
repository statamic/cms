<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class AlphaDashSpace implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Same as `alpha_dash`, but allows spaces
        if (! preg_match('/\A[ \pL\pM\pN_-]+\z/u', $value)) {
            $fail('statamic::validation.alpha_dash')->translate();
        }
    }
}
