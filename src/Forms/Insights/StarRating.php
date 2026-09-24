<?php

namespace Statamic\Forms\Insights;

use Statamic\Forms\Fields\FormField;
use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Summary\FieldResponses;

class StarRating extends Insight
{
    public function supports(): array
    {
        return [FormValueType::Number];
    }

    public function appliesTo(FormField $field): bool
    {
        return parent::appliesTo($field)
            && filled($field->fieldtype()->insightConfig()['total'] ?? null);
    }

    public function props(FieldResponses $responses): array
    {
        return [
            'average' => round($responses->numeric()?->average() ?? 0, 1),
            'total' => (int) $this->config('total'),
        ];
    }
}
