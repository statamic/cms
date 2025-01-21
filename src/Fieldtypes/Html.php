<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Html extends Fieldtype
{
    protected $categories = ['special'];
    protected $icon = 'html';

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance'),
                'fields' => [
                    'html' => [
                        'display' => 'HTML',
                        'instructions' => __('statamic::fieldtypes.html.config.html_instruct'),
                        'type' => 'code',
                        'mode' => 'htmlmixed',
                        'mode_selectable' => false,
                    ],
                ],
            ],
        ];
    }
}
