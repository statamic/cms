<?php

namespace Statamic\Rules;

use Closure;
use DateTime;
use Illuminate\Contracts\Validation\ValidationRule;

class TimeFieldtype implements ValidationRule
{
    public function __construct(private $fieldtype)
    {
        //
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $format = $this->fieldtype->config('seconds_enabled') ? 'H:i:s' : 'H:i';

        if (! $this->matchesFormat($value, $format)) {
            $fail('statamic::validation.time')->translate();
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
