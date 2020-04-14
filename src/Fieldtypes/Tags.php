<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

class Tags extends Fieldtype
{
    protected function configFieldItems(): array
    {
        return [
            'placeholder' => [
                'display' => __('Placeholder'),
                'instructions' => __('statamic::fieldtypes.select.config.placeholder'),
                'type' => 'text',
                'default' => '',
                'width' => 50,
            ],
        ];
    }

    public function preProcess($data)
    {
        return ($data) ? $data : [];
    }
}
