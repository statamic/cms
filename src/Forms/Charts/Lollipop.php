<?php

namespace Statamic\Forms\Charts;

use Statamic\Forms\Fields\FormValueType;

class Lollipop extends Chart
{
    protected static $title = 'Lollipop chart';

    protected ?string $component = 'ui-horizontal-lollipop-chart';
    protected ?string $icon = 'charts-bar-horizontal';
    protected ?int $limit = 5;

    public function supports(): array
    {
        return [FormValueType::Number, FormValueType::Boolean, FormValueType::Choice, FormValueType::Choices];
    }
}
