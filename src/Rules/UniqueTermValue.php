<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Lang;
use Statamic\Facades\Term;

class UniqueTermValue implements ValidationRule
{
    public function __construct(
        private $taxonomy = null,
        private $except = null,
        private $site = null,
    ) {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Term::query();

        if ($this->taxonomy) {
            $query->where('taxonomy', $this->taxonomy);
        }

        if ($this->site) {
            $query->where('site', $this->site);
        }

        $existing = $query
            ->when(
                is_array($value),
                fn ($query) => $query->whereIn($attribute, $value),
                fn ($query) => $query->where($attribute, $value)
            )
            ->first();

        if (! $existing) {
            return;
        }

        if ($this->except == $existing->id()) {
            return;
        }

        $key = Lang::has('validation.unique_term_value')
            ? 'validation.unique_term_value'
            : 'statamic::validation.unique_term_value';

        $fail($key)->translate();
    }
}
