<?php

namespace Statamic\Forms\Charts;

class Pie extends Chart
{
    protected static $title = 'Pie chart';

    protected ?string $component = 'ui-pie-chart';
    protected ?string $icon = 'money-graph-pie-chart';
    protected ?int $limit = 4;
}
