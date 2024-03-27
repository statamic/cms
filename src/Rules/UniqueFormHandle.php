<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Facades\Form;

class UniqueFormHandle implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Form::find($value)) {
            $fail('statamic::validation.unique_form_handle')->translate();
        }
    }
}
