<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Charts\VerticalBar;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Insights\Average;
use Statamic\Forms\Insights\MinMax;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Number extends FormFieldtype
{
    protected static $fieldtype = 'integer';
    protected $description = 'Collects a number. You can set minimum and maximum values.';
    protected $icon = 'number';
    protected $categories = ['number'];
    protected $keywords = ['integer', 'float'];
    protected $order = 1;

    public function configFieldItems(): array
    {
        return [
            'min' => [
                'display' => __('Min'),
                'instructions' => __('statamic::fieldtypes.integer.config.min'),
                'type' => 'integer',
                'width' => '50',
            ],
            'max' => [
                'display' => __('Max'),
                'instructions' => __('statamic::fieldtypes.integer.config.max'),
                'type' => 'integer',
                'width' => '50',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'integer',
            'min' => $this->config('min'),
            'max' => $this->config('max'),
            ...Arr::except($this->config(), ['type', 'min', 'max']),
        ];
    }

    public function defaultChart(): ?string
    {
        return VerticalBar::class;
    }

    public function insights(): array
    {
        return [new MinMax, new Average];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'How many cups of coffee do you drink per day?',
            ],
            'value' => 4,
        ];
    }
}
