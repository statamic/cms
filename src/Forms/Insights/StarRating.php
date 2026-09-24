<?php

namespace Statamic\Forms\Insights;

use Statamic\Forms\Summary\FieldResponses;

class StarRating extends Insight
{
    public function props(FieldResponses $responses): array
    {
        return [
            'average' => round($responses->numeric()?->average() ?? 0, 1),
            'total' => (int) $this->config('total'),
        ];
    }
}
