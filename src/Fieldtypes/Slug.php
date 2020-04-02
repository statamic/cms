<?php

namespace Statamic\Fieldtypes;

use Statamic\Fieldtypes\Text;

class Slug extends Text
{
    protected function configFieldItems(): array
    {
        return [
            'generate' => [
                'display' => __('Generate'),
                'type' => 'toggle',
                'default' => true
            ],
        ];
    }
}
