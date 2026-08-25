<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Lang;
use Statamic\Facades\Form;

class UniqueFormHandle implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (Form::find($value)) {
            $key = Lang::has('validation.unique_form_handle')
                ? 'validation.unique_form_handle'
                : 'statamic::validation.unique_form_handle';

            $fail($key)->translate();
        }
    }
}
