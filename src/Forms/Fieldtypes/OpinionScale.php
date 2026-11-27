<?php

namespace Statamic\Forms\Fieldtypes;

use Illuminate\Support\Collection;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Charts\VerticalBar;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Insights\Average;
use Statamic\Forms\Summary\FieldResponses;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class OpinionScale extends FormFieldtype
{
    protected static $fieldtype = 'opinion_scale';
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
                'max' => 10,
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

    public function valueType(): ?FormValueType
    {
        return FormValueType::Number;
    }

    public function defaultChart(): ?string
    {
        return VerticalBar::class;
    }

    public function chartOptions(FieldResponses $responses): ?Collection
    {
        [$min, $max] = $this->normalizedRange();

        return collect(range($min, $max))
            ->map(fn ($value) => new ChartOption((string) $value));
    }

    public function defaultInsights(): array
    {
        return [Average::class];
    }

    public function example(): ?array
    {
        [$min, $max] = $this->field ? $this->normalizedRange() : [0, 5];

        return [
            'config' => [
                'display' => __('statamic::form-fieldtypes.opinion_scale.example.display'),
                'min' => 0,
                'max' => 5,
                'low_label' => __('statamic::form-fieldtypes.opinion_scale.example.low_label'),
                'high_label' => __('statamic::form-fieldtypes.opinion_scale.example.high_label'),
            ],
            'value' => (int) round($min + ($max - $min) * 0.8),
        ];
    }

    private function normalizedRange(): array
    {
        $min = max(0, (int) $this->config('min', 0));
        $max = min(10, (int) $this->config('max', 10));

        return [$min, $max];
    }
}
