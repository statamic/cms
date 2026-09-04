<?php

namespace Statamic\Forms\Charts;

class Lollipop extends Chart
{
    protected static $title = 'Lollipop chart';

    protected ?string $component = 'ui-horizontal-lollipop-chart';
    protected ?string $icon = 'charts-bar-horizontal';
    protected ?int $limit = 5;
}
