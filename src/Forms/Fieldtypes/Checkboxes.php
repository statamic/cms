<?php

namespace Statamic\Forms\Fieldtypes;

use Illuminate\Support\Collection;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Checkboxes extends FormFieldtype
{
    protected static $fieldtype = 'checkboxes';
    protected $description = 'Respondents can select multiple options from a list.';
    protected $icon = 'fieldtype-checkboxes';
    protected $categories = ['choice'];
    protected $order = 4;

    public function configFieldItems(): array
    {
        return [
            'options' => [
                'display' => __('Options'),
                'instructions' => __('statamic::fieldtypes.checkboxes.config.options'),
                'type' => 'array',
                'expand' => true,
                'show_hide_toggle' => true,
                'key_header' => __('Key'),
                'value_header' => __('Label').' ('.__('Optional').')',
                'add_button' => __('Add Option'),
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'checkboxes',
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
        return HorizontalBar::class;
    }

    public function chartOptions(Collection $values): ?Collection
    {
        return collect($this->enabledOptions())
            ->map(fn ($label, $key) => new ChartOption($key, $label, icon: 'checkbox-filled'))
            ->values();
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'What toppings do you want?',
                'options' => [
                    'pepperoni' => 'Pepperoni',
                    'mushrooms' => 'Mushrooms',
                    'pineapple' => 'Pineapple (controversial)',
                    'anchovies' => 'Anchovies (brave choice)',
                ],
            ],
            'value' => ['pepperoni', 'pineapple'],
        ];
    }
}
