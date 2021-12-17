<?php

namespace Statamic\Fieldtypes;

class Slug extends Text
{
    protected $categories = ['special'];

    protected function configFieldItems(): array
    {
        return [
            'generate' => [
                'display' => __('Generate'),
                'type' => 'toggle',
                'default' => true,
            ],
        ];
    }
}
