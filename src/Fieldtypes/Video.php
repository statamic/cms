<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Video extends Fieldtype
{
    protected $categories = ['media'];

    protected function configFieldItems(): array
    {
        return [
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'text',
                'width' => 50,
            ],
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.text.config.placeholder'),
                'type' => 'text',
                'width' => 50,
            ],
        ];
    }
}
