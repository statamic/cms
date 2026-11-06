<?php

namespace Statamic\Forms\Summary;

final readonly class NumericSummary
{
    public function __construct(
        private int $count,
        private int|float $sum,
        private int|float $min,
        private int|float $max,
    ) {
    }

    public static function fromCounts(array $counts): ?self
    {
        $count = 0;
        $sum = 0;
        $min = null;
        $max = null;

        foreach ($counts as $key => $occurrences) {
            if (! is_numeric($key)) {
                continue;
            }

            $number = $key + 0;
            $sum += $number * $occurrences;
            $count += $occurrences;
            $min = $min === null ? $number : min($min, $number);
            $max = $max === null ? $number : max($max, $number);
        }

        return $count ? new self($count, $sum, $min, $max) : null;
    }

    public function count(): int
    {
        return $this->count;
    }

    public function sum(): int|float
    {
        return $this->sum;
    }

    public function min(): int|float
    {
        return $this->min;
    }

    public function max(): int|float
    {
        return $this->max;
    }

    public function average(): float
    {
        return $this->sum / $this->count;
    }
}
