<?php

namespace Statamic\Forms\Fieldtypes;

use Illuminate\Support\Collection;
use Statamic\Forms\Charts\HorizontalBar;
use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Forms\Fields\FormValueType;
use Statamic\Forms\Fieldtypes\Concerns\HasHideableOptions;
use Statamic\Forms\Summary\FieldResponses;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Checkboxes extends FormFieldtype
{
    use HasHideableOptions;

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
            'options' => $this->options(),
            'validate' => $this->validationRules(),
            ...Arr::except($this->config(), ['type', 'options', 'validate']),
        ];
    }

    public function valueType(): ?FormValueType
    {
        return FormValueType::Choices;
    }

    public function defaultChart(): ?string
    {
        return HorizontalBar::class;
    }

    public function chartOptions(FieldResponses $responses): ?Collection
    {
        return $this->hideableChartOptions($responses, 'checkbox-filled');
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
