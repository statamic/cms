<?php

namespace Statamic\Forms\Insights;

use Illuminate\Support\Collection;

class StarRating extends Insight
{
    public function __construct(private int $total)
    {
    }

    public function props(Collection $values): array
    {
        $values = $values->filter(fn ($value) => is_numeric($value));

        return [
            'average' => round($values->avg() ?? 0, 1),
            'total' => $this->total,
        ];
    }
}
