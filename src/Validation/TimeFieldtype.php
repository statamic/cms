<?php

namespace Statamic\Validation;

use DateTime;
use Illuminate\Contracts\Validation\InvokableRule;

class TimeFieldtype implements InvokableRule
{
    protected $fieldtype;

    public function __construct($fieldtype)
    {
        $this->fieldtype = $fieldtype;
    }

    public function __invoke($attribute, $value, $fail)
    {
        $format = $this->fieldtype->config('seconds_enabled') ? 'H:i:s' : 'H:i';

        if (! $this->matchesFormat($value, $format)) {
            return $fail('statamic::validation.time')->translate();
        }
    }

    private function matchesFormat($value, string $format)
    {
        if (! $value) {
            return false;
        }

        $date = DateTime::createFromFormat('!'.$format, $value);

        return $date && $date->format($format) == $value;
    }
}
