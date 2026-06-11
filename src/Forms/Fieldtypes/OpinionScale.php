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
                'display' => __('Min Value'),
                'type' => 'integer',
                'default' => 0,
                'min' => 0,
                'max' => 1,
                'width' => 50,
            ],
            'max' => [
                'display' => __('Max Value'),
                'type' => 'integer',
                'default' => 10,
                'min' => 1,
                'max' => 11,
                'width' => 50,
            ],
            'low_label' => [
                'display' => __('Low Label'),
                'instructions' => __('Shown below the lowest value.'),
                'type' => 'text',
            ],
            'middle_label' => [
                'display' => __('Middle Label'),
                'instructions' => __('Shown below the middle value.'),
                'type' => 'text',
            ],
            'high_label' => [
                'display' => __('High Label'),
                'instructions' => __('Shown below the highest value.'),
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
            'low_label' => $this->config('low_label'),
            'middle_label' => $this->config('middle_label'),
            'high_label' => $this->config('high_label'),
            ...Arr::except($this->config(), ['type', 'min', 'max', 'low_label', 'middle_label', 'high_label']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => __('How likely are you to recommend us?'),
                'min' => 0,
                'max' => 5,
                'low_label' => __('Not likely'),
                'high_label' => __('Very likely'),
            ],
            'value' => 8,
        ];
    }

    private function normalizedRange(): array
    {
        $configuredMin = $this->config('min');
        $min = ($configuredMin === null || $configuredMin === '') ? 0 : (int) $configuredMin;
        $min = max(0, $min);

        $configuredMax = $this->config('max');
        $max = ($configuredMax === null || $configuredMax === '') ? 10 : (int) $configuredMax;
        $max = min(10, $max);

        return [$min, $max];
    }
}
