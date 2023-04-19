<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;
use Statamic\Support\Str;

class Color extends Fieldtype
{
    protected $categories = ['special'];

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
                ],
            ],
        ];
    }

    public function process($data)
    {
        return Str::ensureLeft($data, '#');
    }
}
