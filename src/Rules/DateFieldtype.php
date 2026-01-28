<?php

namespace Statamic\Rules;

use Carbon\Carbon;
use Closure;
use DateTime;
use Illuminate\Contracts\Validation\ValidationRule;
use Statamic\Support\Arr;

class DateFieldtype implements ValidationRule
{
    public function __construct(private $fieldtype)
    {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value) || $value instanceof Carbon) {
            return;
        }

        if ($this->fieldtype->config('mode') === 'single') {
            if ($value && ! $this->validDateFormat($value)) {
                $fail('statamic::validation.date')->translate();

                return;
            }
        }

        if ($this->fieldtype->config('mode') === 'range') {
            $date = $value;

            if (isset($date['start']) && $date['start'] instanceof Carbon) {
                // It was already processed.
                return;
            }

            if (! $date && $this->fieldtype->isRequired()) {
                $fail('statamic::validation.date_fieldtype_date_required')->translate();

                return;
            }

            if (! Arr::has($date, 'start')) {
                $fail('statamic::validation.date_fieldtype_start_date_required')->translate();

                return;
            }
            if (! Arr::has($date, 'end')) {
                $fail('statamic::validation.date_fieldtype_end_date_required')->translate();

                return;
            }

            if (! $date['start'] && ! $date['end']) {
                $fail('statamic::validation.date_fieldtype_date_required')->translate();

                return;
            }

            if (! $date['start'] && $date['end']) {
                $fail('statamic::validation.date_fieldtype_start_date_required')->translate();

                return;
            }

            if (! $date['end'] && $date['start']) {
                $fail('statamic::validation.date_fieldtype_end_date_required')->translate();

                return;
            }

            if ($date['start'] && ! $this->validDateFormat($date['start'])) {
                $fail('statamic::validation.date_fieldtype_start_date_invalid')->translate();

                return;
            }

            if ($date['end'] && ! $this->validDateFormat($date['end'])) {
                $fail('statamic::validation.date_fieldtype_end_date_invalid')->translate();

                return;
            }
        }
    }

    private function validDateFormat($value)
    {
        $format = 'Y-m-d\TH:i:s.v\Z';

        $date = DateTime::createFromFormat('!'.$format, $value);

        return $date && $date->format($format) == $value;
    }
}
