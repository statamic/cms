<?php

namespace Statamic\Validation;

use Carbon\Carbon;
use DateTime;
use Statamic\Support\Arr;

class DateFieldtype
{
    private $fieldtype;

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;
    }

    public function __invoke($value)
    {
        if (is_null($value) || $value instanceof Carbon) {
            return;
        }

        if (! is_array($value)) {
            return __('statamic::validation.array');
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
                return __('statamic::validation.date');
            }
        }

        if ($this->fieldtype->config('mode') === 'range') {
            if (isset($value['start'])) {
                // It was already processed.
                return;
            }

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
            return __('statamic::validation.time');
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
        return __('statamic::validation.date_fieldtype_'.$message);
    }
}
