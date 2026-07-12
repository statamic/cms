<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Facades\User;

class EmailAvailable implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (User::query()->where('email', trim($value))->count() !== 0) {
            $fail('statamic::validation.email_available')->translate();
        }
    }
}
