<?php

namespace Statamic\Forms\Insights;

use Illuminate\Support\Collection;

class Checked extends Insight
{
    public function props(Collection $values): array
    {
        $total = $values->count();
        $checked = $values->filter(fn ($value) => filter_var($value, FILTER_VALIDATE_BOOLEAN))->count();

        return [
            'count' => $checked,
            'percent' => $total ? (int) round($checked / $total * 100) : 0,
        ];
    }
}
