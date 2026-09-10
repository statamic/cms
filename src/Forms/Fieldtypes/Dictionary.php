<?php

namespace Statamic\Forms\Fieldtypes;

use Illuminate\Support\Collection;
use Statamic\Forms\Charts\ChartOption;
use Statamic\Forms\Charts\Lollipop;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Dictionary extends FormFieldtype
{
    protected static $fieldtype = 'dictionary';
    protected $description = 'Select from a predefined list like countries, timezones, or currencies.';
    protected $icon = 'fieldtype-dictionary';
    protected $categories = ['choice'];
    protected $keywords = ['countries', 'timezones', 'currencies'];

    public function configFieldItems(): array
    {
        return [
            'dictionary' => [
                'type' => 'dictionary_fields',
                'hide_display' => true,
                'full_width_setting' => true,
            ],
            'placeholder' => [
                'display' => __('Placeholder'),
                'type' => 'text',
                'default' => '',
            ],
            'max_items' => [
                'display' => __('Max Selections'),
                'instructions' => __('statamic::messages.max_items_instructions'),
                'min' => 1,
                'type' => 'integer',
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'dictionary',
            ...Arr::except($this->config(), ['type']),
        ];
    }

    public function defaultChart(): ?string
    {
        return Lollipop::class;
    }

    public function chartOptions(Collection $values): ?Collection
    {
        $dictionary = $this->toField()->fieldtype()->dictionary();

        return $values->flatten()
            ->countBy(fn ($value) => (string) $value)
            ->sortDesc()
            ->keys()
            ->map(fn ($value) => new ChartOption((string) $value, $dictionary->get((string) $value)?->label()));
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => 'Where have you been on vacation recently?',
                'dictionary' => 'countries',
            ],
            'value' => ['GBR', 'USA', 'NLD', 'DEU'],
        ];
    }
}
