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

        if (! is_array($value)) {
            $fail('statamic::validation.array')->translate();

            return;
        }

        if ($this->fieldtype->config('mode') === 'single') {
            if (! Arr::has($value, 'date')) {
                $fail('statamic::validation.date_fieldtype_date_required')->translate();

                return;
            }

            $date = $value['date'];

            if ($this->fieldtype->isRequired() && ! $date) {
                $fail('statamic::validation.date_fieldtype_date_required')->translate();

                return;
            }

            if ($date && ! $this->validDateFormat($date)) {
                $fail('statamic::validation.date')->translate();

                return;
            }
        }

        if ($this->fieldtype->config('mode') === 'range') {
            if (isset($value['start'])) {
                // It was already processed.
                return;
            }

            $date = $value['date'];

            if (! $date && $this->fieldtype->isRequired()) {
                $fail('statamic::validation.date_fieldtype_date_required')->translate();

                return;
            }

            if (! $date) {
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

            if ($this->fieldtype->isRequired() && ! $date['start'] && ! $date['end']) {
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

        if (! $this->timeEnabled()) {
            return;
        }

        if (! Arr::has($value, 'time')) {
            $fail('statamic::validation.date_fieldtype_time_required')->translate();

            return;
        }

        $time = $value['time'];

        if ($this->fieldtype->isRequired() && ! $time) {
            $fail('statamic::validation.date_fieldtype_time_required')->translate();

            return;
        }

        if ($time && ! $this->validTimeFormat($time)) {
            $fail('statamic::validation.time')->translate();

            return;
        }
    }

    private function validDateFormat($value)
    {
        return $this->matchesFormat($value, 'Y-m-d');
    }

    private function validTimeFormat($value)
    {
        $format = $this->fieldtype->config('time_seconds_enabled') ? 'H:i:s' : 'H:i';

        return $this->matchesFormat($value, $format);
    }

    private function matchesFormat($value, string $format)
    {
        if (! $value) {
            return false;
        }

        $date = DateTime::createFromFormat('!'.$format, $value);

        return $date && $date->format($format) == $value;
    }

    private function timeEnabled()
    {
        return $this->fieldtype->config('time_enabled');
    }
}
