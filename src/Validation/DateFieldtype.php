<?php

namespace Statamic\Validation;

use DateTime;
use Illuminate\Contracts\Validation\InvokableRule;
use Statamic\Support\Arr;

class DateFieldtype implements InvokableRule
{
    private $fieldtype;
    private $fail;

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;
    }

    public function __invoke($attribute, $value, $fail)
    {
        $this->fail = $fail;

        if (! is_array($value)) {
            return $fail('statamic::validation.array')->translate();
        }

        if ($this->fieldtype->config('mode') === 'single') {
            if (! Arr::has($value, 'date')) {
                return $this->fail('date_required');
            }

            $date = $value['date'];

            if ($this->fieldtype->isRequired() && ! $date) {
                return $this->fail('date_required');
            }

            if ($date && ! $this->validDateFormat($date)) {
                return $fail('statamic::validation.date')->translate();
            }
        }

        if ($this->fieldtype->config('mode') === 'range') {
            $date = $value['date'];

            if (! $date && $this->fieldtype->isRequired()) {
                return $this->fail('date_required');
            }

            if (! $date) {
                return;
            }

            if (! Arr::has($date, 'start')) {
                return $this->fail('start_date_required');
            }
            if (! Arr::has($date, 'end')) {
                return $this->fail('end_date_required');
            }

            if ($this->fieldtype->isRequired() && ! $date['start'] && ! $date['end']) {
                return $this->fail('date_required');
            }

            if (! $date['start'] && $date['end']) {
                return $this->fail('start_date_required');
            }

            if (! $date['end'] && $date['start']) {
                return $this->fail('end_date_required');
            }

            if ($date['start'] && ! $this->validDateFormat($date['start'])) {
                return $this->fail('start_date_invalid');
            }

            if ($date['end'] && ! $this->validDateFormat($date['end'])) {
                return $this->fail('end_date_invalid');
            }
        }

        if (! $this->timeEnabled()) {
            return;
        }

        if (! Arr::has($value, 'time')) {
            return $this->fail('time_required');
        }

        $time = $value['time'];

        if ($this->fieldtype->isRequired() && ! $time) {
            return $this->fail('time_required');
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

    private function fail($message)
    {
        call_user_func($this->fail, 'statamic::validation.date_fieldtype_'.$message)->translate();
    }
}
