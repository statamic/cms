<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class ButtonGroup extends Fieldtype
{
    use HasSelectOptions;

    protected $categories = ['controls'];
    protected $indexComponent = 'tags';

    protected function configFieldItems(): array
    {
        return [
            'options' => [
                'display' => __('Options'),
                'instructions' => __('statamic::fieldtypes.radio.config.options'),
                'type' => 'array',
                'value_header' => __('Label').' ('.__('Optional').')',
            ],
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'text',
                'width' => 50,
            ],
        ];
    }
}
