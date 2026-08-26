<?php

namespace Statamic\Forms\Charts;

class HorizontalBar extends Chart
{
    protected static $title = 'Bar chart';

    protected ?string $icon = 'charts-bar-horizontal';
    protected ?int $limit = 5;
}
