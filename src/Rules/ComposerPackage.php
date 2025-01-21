<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ComposerPackage implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! preg_match("/^[^\/\s]+\/[^\/\s]+$/", $value)) {
            $fail('statamic::validation.composer_package')->translate();
        }
    }
}
