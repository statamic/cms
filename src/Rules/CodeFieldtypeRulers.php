<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CodeFieldtypeRulers implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        foreach ($value as $key => $val) {
            if (! is_int($key) || ! in_array($val, ['dashed', 'solid'])) {
                $fail('statamic::validation.code_fieldtype_rulers')->translate();
            }
        }
    }
}
