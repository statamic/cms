<?php

namespace Statamic\Forms\Charts;

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
}
