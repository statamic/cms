<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Facades\Entry;

class UniqueEntryValue implements ValidationRule
{
    public function __construct(
        private $collection = null,
        private $except = null,
        private $site = null,
    ) {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $query = Entry::query();

        if ($this->collection) {
            $query->where('collection', $this->collection);
        }

        if ($this->site) {
            $query->where('site', $this->site);
        }

        $existing = $query
            ->where($attribute, $value)
            ->first();

        if (! $existing) {
            return;
        }

        if ($this->except == $existing->id()) {
            return;
        }

        $fail('statamic::validation.unique_entry_value')->translate();
    }
}
