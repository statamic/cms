<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Ranking extends FormFieldtype
{
    protected static $fieldtype = 'ranking';
    protected $description = 'A ranking input for ordering items by preference.';
    protected $icon = 'rank';
    protected $categories = ['rate'];
    protected $order = 4;

    public function configFieldItems(): array
    {
        return [
            'options' => [
                'display' => __('Options'),
                'type' => 'array',
                'expand' => true,
                'show_hide_toggle' => true,
                'key_header' => __('Key'),
                'value_header' => __('Label').' ('.__('Optional').')',
                'add_button' => __('Add Option'),
            ],
        ];
    }

    public function preload(): array
    {
        return [
            'options' => collect($this->enabledOptions())
                ->map(fn ($label, $value) => ['value' => $value, 'label' => $label])
                ->values()
                ->all(),
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'ranking',
            'options' => $this->enabledOptions(),
            ...Arr::except($this->config(), ['type', 'options']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => __('Rank your favorite seasons'),
                'options' => [
                    'spring' => __('Spring'),
                    'summer' => __('Summer'),
                    'autumn' => __('Autumn'),
                    'winter' => __('Winter'),
                ],
            ],
            'value' => ['summer', 'spring', 'autumn', 'winter'],
        ];
    }

    private function enabledOptions(): array
    {
        return collect($this->config('options'))
            ->reject(fn ($option, $key): bool => $this->isBlankOption($option, $key))
            ->mapWithKeys(fn ($option, $key): array => [
                is_array($option) ? $option['key'] : $key => is_array($option) ? $option['value'] : $option,
            ])
            ->all();
    }

    private function isBlankOption(mixed $option, mixed $key): bool
    {
        if (is_array($option) && ($option['hidden'] ?? false) === true) {
            return true;
        }

        $optionKey = is_array($option) ? ($option['key'] ?? null) : $key;

        return $optionKey === null || $optionKey === '' || $optionKey === 'null';
    }
}
