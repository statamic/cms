<?php

namespace Statamic\Forms\Charts;

use Illuminate\Support\Collection;

class Pie extends Chart
{
    protected static $title = 'Pie chart';

    protected ?string $component = 'ui-pie-chart';
    protected ?string $icon = 'money-graph-pie-chart';
    protected ?int $limit = 4;

    // The pie keeps its shape while the "other" slice is highlighted and its items are listed.
    protected function drilldown(Collection $items, Collection $other, int $total): array
    {
        return [
            ...parent::drilldown($items, $other, $total),
            'segments' => $items->all(),
        ];
    }
}
