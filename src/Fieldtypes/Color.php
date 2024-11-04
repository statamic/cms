<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Color extends Fieldtype
{
    protected $categories = ['special'];
    protected $keywords = ['rgb', 'hex', 'colour'];

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance & Behavior'),
                'fields' => [
                    'swatches' => [
                        'display' => __('Swatches'),
                        'instructions' => __('statamic::fieldtypes.color.config.swatches'),
                        'type' => 'list',
                        'add_button' => __('Add Color'),
                    ],
                    'allow_any' => [
                        'display' => __('Allow Any Color'),
                        'instructions' => __('statamic::fieldtypes.color.config.allow_any'),
                        'type' => 'toggle',
                        'default' => true,
                    ],
                    'default' => [
                        'display' => __('Default Color'),
                        'instructions' => __('statamic::fieldtypes.color.config.default'),
                        'type' => 'color',
                    ],
                ],
            ],
        ];
    }
}
