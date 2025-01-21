<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
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

        $fail('statamic::validation.unique_term_value')->translate();
    }
}
