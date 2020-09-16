<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Range extends Fieldtype
{
    protected function configFieldItems(): array
    {
        return [
            'hidden' => [
                'type' => 'hidden',
                'width' => 50,
            ],
            'step' => [
                'display' => __('Step'),
                'instructions' => __('statamic::fieldtypes.range.config.step'),
                'type' => 'integer',
                'default' => 1,
                'width' => 50,
            ],
            'min' => [
                'display' => __('Min'),
                'instructions' => __('statamic::fieldtypes.range.config.min'),
                'type' => 'integer',
                'default' => 0,
                'width' => 33,
            ],
            'max' => [
                'display' => __('Max'),
                'instructions' => __('statamic::fieldtypes.range.config.max'),
                'type' => 'integer',
                'default' => 100,
                'width' => 33,
            ],
            'default' => [
                'display' => __('Default'),
                'instructions' => __('statamic::fieldtypes.any.config.default'),
                'type' => 'integer',
                'default' => null,
                'width' => 33,
            ],
            'prepend' => [
                'display' => __('Prepend'),
                'instructions' => __('statamic::fieldtypes.range.config.prepend'),
                'type' => 'text',
                'width' => 50,
            ],
            'append' => [
                'display' => __('Append'),
                'instructions' => __('statamic::fieldtypes.range.config.append'),
                'type' => 'text',
                'width' => 50,
            ],
        ];
    }
}
