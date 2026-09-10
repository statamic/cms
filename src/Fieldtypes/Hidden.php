<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

use function Statamic\trans as __;

class Hidden extends Fieldtype
{
    protected $categories = ['special'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Data & Format'),
                'fields' => [
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'text',
                    ],
                ],
            ],
        ];
    }
}
