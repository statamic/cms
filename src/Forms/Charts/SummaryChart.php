<?php

namespace Statamic\Forms\Charts;

use Illuminate\Support\Collection;
use Statamic\Forms\Fields\FormField;

readonly class SummaryChart
{
    public function __construct(private FormField $field, private Chart $chart)
    {
    }

    public function field(): FormField
    {
        return $this->field;
    }

    public function chart(): Chart
    {
        return $this->chart;
    }

    public function props(Collection $values): array
    {
        return $this->chart->props($values, $this->field->fieldtype()->chartOptions($values));
    }
}
