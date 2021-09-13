<?php

namespace Statamic\View\Antlers\Language\Runtime\Sandbox\TypeBoxing;

use Illuminate\Support\Traits\Macroable;
use Statamic\Support\Str;

class NumericBoxObject
{
    use Macroable, AntlersBoxedStandardMethods;

    protected $value = 0;

    public function __construct($value)
    {
        $this->value = $value;
    }

    public function toExponential($fraction_digits = 2)
    {
        if (! is_int($fraction_digits)) {
            $fraction_digits = 2;
        }

        $formatString = '%.'.$fraction_digits.'E';

        return mb_strtolower(sprintf($formatString, $this->value));
    }

    public function toPrecision($precision)
    {
        if ($this->value == 0) {
            return 0;
        }

        $exponent = floor(log10(abs($this->value)) + 1);
        $significand =
            round(
                ($this->value / pow(10, $exponent))
                * pow(10, $precision)
            )
            / pow(10, $precision);

        return $significand * pow(10, $exponent);
    }

    public function toFixed($precision = 0, $dec_point = '.', $thousands_sep = ',')
    {
        return $this->format($precision, $dec_point, $thousands_sep);
    }

    public function format($precision = 0, $dec_point = '.', $thousands_sep = ',')
    {
        return number_format($this->value, $precision, $dec_point, $thousands_sep);
    }

    public function fileSizeForHumans($decimals = 2)
    {
        return Str::fileSizeForHumans($this->value, $decimals);
    }

    public function timeForHumans()
    {
        return Str::timeForHumans($this->value);
    }
}
