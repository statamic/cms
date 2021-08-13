<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Html extends Fieldtype
{
    protected static $title = 'HTML';
    protected $icon = 'html';

    protected $configFields = [
        'html' => [
            'display' => 'HTML',
            'type' => 'code',
            'mode' => 'htmlmixed',
        ],
    ];

    protected function configFieldItems(): array
    {
        return [
            'default' => [
                'display' => __('Default Value'),
                'instructions' => __('statamic::messages.fields_default_instructions'),
                'type' => 'textarea',
                'width' => 100,
            ],
        ];
    }
}
