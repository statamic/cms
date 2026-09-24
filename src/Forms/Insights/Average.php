<?php

namespace Statamic\Forms\Insights;

use Statamic\Forms\Summary\FieldResponses;

class Average extends Insight
{
    public function __construct(private ?string $prefix = null, private ?string $suffix = null, private int $decimals = 1)
    {
    }

    public function props(FieldResponses $responses): array
    {
        return array_filter([
            'average' => number_format($responses->numeric()?->average() ?? 0, $this->decimals),
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
        ], fn ($value) => $value !== null);
    }
}
