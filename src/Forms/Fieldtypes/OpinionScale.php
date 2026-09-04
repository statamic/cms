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
                'min' => 0,
                'max' => 1,
                'width' => 50,
                'placeholder' => 0,
            ],
            'max' => [
                'display' => __('Max Value'),
                'type' => 'integer',
                'min' => 1,
                'max' => 11,
                'width' => 50,
                'placeholder' => 10,
            ],
            'low_label' => [
                'display' => __('Low Label'),
                'instructions' => __('statamic::form-fieldtypes.opinion_scale.config.low_label.instructions'),
                'type' => 'text',
            ],
            'middle_label' => [
                'display' => __('Middle Label'),
                'instructions' => __('statamic::form-fieldtypes.opinion_scale.config.middle_label.instructions'),
                'type' => 'text',
            ],
            'high_label' => [
                'display' => __('High Label'),
                'instructions' => __('statamic::form-fieldtypes.opinion_scale.config.high_label.instructions'),
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
                'display' => 'How likely are you to recommend us?',
                'min' => 0,
                'max' => 5,
                'low_label' => 'Not likely',
                'high_label' => 'Very likely',
            ],
            'value' => 8,
        ];
    }

    private function normalizedRange(): array
    {
        $min = max(0, (int) $this->config('min', 0));
        $max = min(10, (int) $this->config('max', 10));

        return [$min, $max];
    }
}
