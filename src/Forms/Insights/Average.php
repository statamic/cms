<?php

namespace Statamic\Forms\Insights;

use Statamic\Forms\Summary\FieldResponses;

class Average extends Insight
{
    protected array $defaults = ['decimals' => 1];

    public function props(FieldResponses $responses): array
    {
        return array_filter([
            'average' => number_format($responses->numeric()?->average() ?? 0, (int) $this->config('decimals')),
            'prefix' => $this->config('prefix'),
            'suffix' => $this->config('suffix'),
        ], fn ($value) => $value !== null);
    }
}
