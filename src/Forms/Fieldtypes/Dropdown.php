<?php

namespace Statamic\Forms\Fieldtypes;

use Statamic\Forms\Fields\FormFieldtype;
use Statamic\Support\Arr;

use function Statamic\trans as __;

class Dropdown extends FormFieldtype
{
    protected static $fieldtype = 'select';
    protected $description = 'A dropdown list where respondents pick from your options.';
    protected $icon = 'fieldtype-select';
    protected $categories = ['choice'];

    public function configFieldItems(): array
    {
        return [
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                'type' => 'text',
                'default' => '',
                'width' => '50',
            ],
            'options' => [
                'display' => __('Options'),
                'instructions' => __('statamic::fieldtypes.select.config.options'),
                'type' => 'array',
                'expand' => true,
                'key_header' => __('Key'),
                'value_header' => __('Label').' ('.__('Optional').')',
                'add_button' => __('Add Option'),
                'width' => '50',
                'show_hide_toggle' => true,
            ],
            'multiple' => [
                'display' => __('Multiple'),
                'instructions' => __('statamic::fieldtypes.select.config.multiple'),
                'type' => 'toggle',
                'default' => false,
                'width' => '50',
            ],
            'max_selections' => [
                'display' => __('Max Selections'),
                'instructions' => __('statamic::messages.max_items_instructions'),
                'min' => 1,
                'type' => 'integer',
                'width' => '50',
                'if' => ['multiple' => true],
            ],
        ];
    }

    public function toFieldArray(): array
    {
        return [
            'type' => 'select',
            'options' => $this->config('options'),
            'placeholder' => $this->config('placeholder'),
            'multiple' => $this->config('multiple'),
            'max_items' => $this->config('max_selections', $this->config('max_items')),
            ...Arr::except($this->config(), ['type', 'options', 'placeholder', 'multiple', 'max_items']),
        ];
    }

    public function example(): ?array
    {
        return [
            'config' => [
                'display' => "What's your go-to karaoke song?",
                'placeholder' => 'Select a song...',
                'options' => [
                    'bohemian' => 'Bohemian Rhapsody',
                    'wrecking' => 'I Will Survive',
                    'sweet' => 'Sweet Caroline',
                    'never' => 'Never Gonna Give You Up',
                ],
            ],
            'value' => 'never',
        ];
    }
}
