<?php

namespace Statamic\Forms\Charts;

use Statamic\Forms\Fields\FormValueType;

class VerticalBar extends Chart
{
    protected static $title = 'Column chart';

    protected ?string $component = 'ui-vertical-bar-chart';
    protected ?string $icon = 'chart-increase';
    protected ?int $limit = 12;

    public function supports(): array
    {
        return [FormValueType::Number, FormValueType::Boolean, FormValueType::Choice, FormValueType::Choices];
    }
}
