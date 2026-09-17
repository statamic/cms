<?php

namespace Statamic\Forms\Insights;

use Illuminate\Support\Collection;

class MinMax extends Insight
{
    public function __construct(private ?string $prefix = null, private ?string $suffix = null, private int $decimals = 0)
    {
    }

    public function props(Collection $values): array
    {
        $values = $values->filter(fn ($value) => is_numeric($value));

        return array_filter([
            'min' => number_format($values->min() ?? 0, $this->decimals),
            'max' => number_format($values->max() ?? 0, $this->decimals),
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
        ], fn ($value) => $value !== null);
    }
}
