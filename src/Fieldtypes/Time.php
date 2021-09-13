<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Time extends Fieldtype
{
    protected function configFieldItems(): array
    {
        return [
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'text',
                'width' => 50,
            ],
        ];
    }
}
