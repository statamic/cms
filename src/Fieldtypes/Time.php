<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Time extends Fieldtype
{
    protected function configFieldItems(): array
    {
        return [
            'seconds_enabled'  => [
                'display' => __('Show Seconds'),
                'instructions' => __('statamic::fieldtypes.time.config.seconds_enabled'),
                'type' => 'toggle',
                'default' => false,
                'width' => 50,
            ],
        ];
    }
}
