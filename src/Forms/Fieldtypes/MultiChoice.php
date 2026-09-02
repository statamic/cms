<?php

namespace Statamic\Forms\Fieldtypes;

use Illuminate\Support\Collection;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Charts\Pie;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class MultiChoice extends FormFieldtype
{
    protected static $fieldtype = 'radio';
    protected $description = 'A question with a range of answer options. Respondents can only choose one answer.';
    protected $icon = 'fieldtype-radio';
    protected $categories = ['choice'];
    protected $keywords = ['radio'];
    protected $order = 3;

    public function configFieldItems(): array
    {
        return [
            'options' => [
                'display' => __('Options'),
                'instructions' => __('statamic::fieldtypes.radio.config.options'),
                'type' => 'array',
                'expand' => true,
                'show_hide_toggle' => true,
                'field' => [
                    'type' => 'text',
                ],
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'radio',
            'options' => $this->enabledOptions(),
            ...Arr::except($this->config(), ['type', 'options']),
        ];
    }

    private function enabledOptions(): array
    {
        return collect($this->config('options'))
            ->reject(fn ($option): bool => is_array($option) && ($option['hidden'] ?? false) === true)
            ->mapWithKeys(fn ($option, $key): array => [
                is_array($option) ? $option['key'] : $key => is_array($option) ? $option['value'] : $option,
            ])
            ->all();
    }

    public function defaultChart(): ?string
    {
        return Pie::class;
    }

    public function chartOptions(Collection $values): ?Collection
    {
        return collect($this->enabledOptions())
            ->map(fn ($label, $key) => new ChartOption($key, $label))
            ->values();
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'Why were you late to work?',
                'options' => [
                    'traffic' => 'Traffic was bad',
                    'alarm' => "My alarm didn't go off",
                    'racoon' => 'A raccoon stole my keys',
                ],
            ],
            'value' => 'racoon',
        ];
    }
}
