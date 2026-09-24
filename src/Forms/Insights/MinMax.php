<?php

namespace Statamic\Forms\Insights;

use Statamic\Forms\Summary\FieldResponses;

class MinMax extends Insight
{
    protected array $defaults = ['decimals' => 0];

    public function props(FieldResponses $responses): array
    {
        $numeric = $responses->numeric();
        $decimals = (int) $this->config('decimals');

        return array_filter([
            'min' => number_format($numeric?->min() ?? 0, $decimals),
            'max' => number_format($numeric?->max() ?? 0, $decimals),
            'prefix' => $this->config('prefix'),
            'suffix' => $this->config('suffix'),
        ], fn ($value) => $value !== null);
    }
}
