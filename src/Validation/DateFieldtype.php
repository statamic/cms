<?php

namespace Statamic\Validation;

use DateTime;
use Illuminate\Contracts\Validation\InvokableRule;
use Statamic\Support\Arr;

class DateFieldtype implements InvokableRule
{
    protected $fieldtype;

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;
    }

    public function __invoke($attribute, $value, $fail)
    {
        if (! is_array($value)) {
            return $fail('statamic::validation.array')->translate();
        }

        if ($this->fieldtype->config('mode') === 'single') {
            if (! Arr::has($value, 'date')) {
                return $fail('statamic::validation.date_fieldtype_date_required')->translate();
            }

            $date = $value['date'];

            if ($this->fieldtype->isRequired() && ! $date) {
                return $fail('statamic::validation.date_fieldtype_date_required')->translate();
            }

            if ($date && ! $this->validDateFormat($date)) {
                return $fail('statamic::validation.date')->translate();
            }
        }

        if ($this->fieldtype->config('mode') === 'range') {
            $date = $value['date'];

            if (! $date && $this->fieldtype->isRequired()) {
                return $fail('statamic::validation.date_fieldtype_date_required')->translate();
            }

            if (! $date) {
                return;
            }

            if (! Arr::has($date, 'start')) {
                return $fail('statamic::validation.date_fieldtype_start_date_required')->translate();
            }
            if (! Arr::has($date, 'end')) {
                return $fail('statamic::validation.date_fieldtype_end_date_required')->translate();
            }

            if ($this->fieldtype->isRequired() && ! $date['start'] && ! $date['end']) {
                return $fail('statamic::validation.date_fieldtype_date_required')->translate();
            }

            if (! $date['start'] && $date['end']) {
                return $fail('statamic::validation.date_fieldtype_start_date_required')->translate();
            }

            if (! $date['end'] && $date['start']) {
                return $fail('statamic::validation.date_fieldtype_end_date_required')->translate();
            }

            if ($date['start'] && ! $this->validDateFormat($date['start'])) {
                return $fail('statamic::validation.date_fieldtype_start_date_invalid')->translate();
            }

            if ($date['end'] && ! $this->validDateFormat($date['end'])) {
                return $fail('statamic::validation.date_fieldtype_end_date_invalid')->translate();
            }
        }

        if (! $this->timeEnabled()) {
            return;
        }

        if (! Arr::has($value, 'time')) {
            return $fail('statamic::validation.date_fieldtype_time_required')->translate();
        }

        $time = $value['time'];

        if ($this->fieldtype->isRequired() && ! $time) {
            return $fail('statamic::validation.date_fieldtype_time_required')->translate();
        }

        if ($time && ! $this->validTimeFormat($time)) {
            return $fail('statamic::validation.time')->translate();
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
