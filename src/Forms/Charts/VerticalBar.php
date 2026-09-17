<?php

namespace Statamic\Forms\Charts;

class VerticalBar extends Chart
{
    protected static $title = 'Column chart';

    protected ?string $component = 'ui-vertical-bar-chart';
    protected ?string $icon = 'chart-increase';
    protected ?int $limit = 12;
}
