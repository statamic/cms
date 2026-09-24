<?php

namespace Statamic\Forms\Insights;

use Statamic\Forms\Summary\FieldResponses;

class MinMax extends Insight
{
    public function __construct(private ?string $prefix = null, private ?string $suffix = null, private int $decimals = 0)
    {
    }

    public function props(FieldResponses $responses): array
    {
        $numeric = $responses->numeric();

        return array_filter([
            'min' => number_format($numeric?->min() ?? 0, $this->decimals),
            'max' => number_format($numeric?->max() ?? 0, $this->decimals),
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
        ], fn ($value) => $value !== null);
    }
}
