<?php

namespace Statamic\Forms\Insights;

use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Summary\FieldResponses;

class Checked extends Insight
{
    public function supports(): array
    {
        return [FormValueType::Boolean];
    }

    public function props(FieldResponses $responses): array
    {
        $total = $responses->total();
        $checked = $responses->counts()
            ->filter(fn ($count, $key) => filter_var($key, FILTER_VALIDATE_BOOLEAN))
            ->sum();

        return [
            'count' => $checked,
            'percent' => $total ? (int) round($checked / $total * 100) : 0,
        ];
    }
}
