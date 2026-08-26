<?php

namespace Statamic\Forms\Insights;

use Illuminate\Support\Collection;

class Average extends Insight
{
    public function __construct(private ?string $prefix = null, private ?string $suffix = null, private int $decimals = 1)
    {
    }

    public function props(Collection $values): array
    {
        $values = $values->filter(fn ($value) => is_numeric($value));

        return array_filter([
            'average' => number_format($values->avg() ?? 0, $this->decimals),
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
        ], fn ($value) => $value !== null);
    }
}
