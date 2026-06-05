<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class OpinionScale extends FormFieldtype
{
    protected static $fieldtype = 'opinion_scale';
    protected $description = 'An opinion scale for measuring agreement or satisfaction.';
    protected $icon = 'scale-up';
    protected $categories = ['rate'];
    protected $order = 2;

    public function configFieldItems(): array
    {
        return [
            'min' => [
                'display' => __('Min'),
                'instructions' => __('The lowest value on the scale.'),
                'type' => 'integer',
                'default' => 0,
                'min' => 0,
                'max' => 1,
                'width' => 50,
            ],
            'max' => [
                'display' => __('Max'),
                'instructions' => __('The highest value on the scale.'),
                'type' => 'integer',
                'default' => 10,
                'min' => 1,
                'max' => 11,
                'width' => 50,
            ],
            'left_label' => [
                'display' => __('Left Label'),
                'instructions' => __('Label shown below the lowest value.'),
                'type' => 'text',
            ],
            'center_label' => [
                'display' => __('Center Label'),
                'instructions' => __('Optional label shown below the middle of the scale.'),
                'type' => 'text',
            ],
            'right_label' => [
                'display' => __('Right Label'),
                'instructions' => __('Label shown below the highest value.'),
                'type' => 'text',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        [$min, $max] = $this->normalizedRange();

        return [
            'type' => 'opinion_scale',
            'min' => $min,
            'max' => $max,
            'scale_values' => range($min, $max),
            'left_label' => $this->config('left_label'),
            'center_label' => $this->config('center_label'),
            'right_label' => $this->config('right_label'),
            ...Arr::except($this->config(), ['type', 'min', 'max', 'left_label', 'center_label', 'right_label']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => __('How likely are you to recommend us?'),
                'min' => 0,
                'max' => 10,
                'left_label' => __('Not likely'),
                'right_label' => __('Very likely'),
            ],
            'value' => 8,
        ];
    }

    private function normalizedRange(): array
    {
        $configuredMin = $this->config('min');
        $min = ($configuredMin === null || $configuredMin === '') ? 0 : (int) $configuredMin;
        $min = $min === 1 ? 1 : 0;

        $configuredMax = $this->config('max');
        $max = ($configuredMax === null || $configuredMax === '') ? 10 : (int) $configuredMax;

        $max = max($min + 1, min($min + 10, $max));

        return [$min, $max];
    }
}
