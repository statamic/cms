<?php

namespace Statamic\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Carbon;
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

        if ($this->isDateValue($value)) {
            $existing = $query
                ->when(
                    is_array($value), // for mode: range
                    fn ($query) => $query->whereJsonContains($attribute, $this->convertDateValue($value)),
                    fn ($query) => $query->where($attribute, $this->convertDateValue($value))
                )
                ->first();
        } else {
            $existing = $query
                ->when(
                    is_array($value),
                    fn ($query) => $query->whereIn($attribute, $value),
                    fn ($query) => $query->where($attribute, $value)
                )
                ->first();
        }

        if (! $existing) {
            return;
        }

        if ($this->except == $existing->id()) {
            return;
        }

        $fail('statamic::validation.unique_entry_value')->translate();
    }

    private function convertDateValue(Carbon|array $value): string|array
    {
        if (! is_array($value)) {
            return $value->toDateString();
        }

        return collect($value)
            ->map(fn ($v) => $v instanceof Carbon ? $v->toDateString() : $v)
            ->toArray();
    }

    private function isDateValue($value): bool
    {
        if ($value instanceof Carbon) {
            return true;
        }

        if (! is_array($value)) {
            return false;
        }

        if (count($value) == 2 && isset($value['start']) && isset($value['end'])) {
            return true;
        }

        return false;
    }
}
