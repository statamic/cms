<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Query\Scopes\Filters\Fields\Textarea as TextareaFilter;

class Textarea extends Fieldtype
{
    protected $categories = ['text'];
    protected $selectableInForms = true;

    protected function configFieldItems(): array
    {
        return [
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                'type' => 'text',
                'width' => 50,
            ],
            'character_limit' => [
                'display' => __('Character Limit'),
                'instructions' => __('statamic::fieldtypes.text.config.character_limit'),
                'type' => 'text',
            ],
            'antlers' => [
                'display' => 'Antlers',
                'instructions' => __('statamic::fieldtypes.any.config.antlers'),
                'type' => 'toggle',
                'width' => 50,
            ],
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'textarea',
                'width' => 100,
            ],
        ];
    }

    public function filter()
    {
        return new TextareaFilter($this);
    }
}
