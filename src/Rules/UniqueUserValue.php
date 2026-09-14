<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Lang;
use Statamic\Facades\User;

class UniqueUserValue implements ValidationRule
{
    public function __construct(
        private $except = null,
        private $column = null,
    ) {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $this->column ??= $attribute;

        $existing = User::query()
            ->when(
                is_array($value),
                fn ($query) => $query->whereIn($this->column, $value),
                fn ($query) => $query->where($this->column, $value)
            )
            ->first();

        if (! $existing) {
            return;
        }

        if ($this->except == $existing->id()) {
            return;
        }

        $key = Lang::has('validation.unique_user_value')
            ? 'validation.unique_user_value'
            : 'statamic::validation.unique_user_value';

        $fail($key)->translate();
    }
}
