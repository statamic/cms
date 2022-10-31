<?php

namespace Statamic\Fieldtypes;

class Slug extends Text
{
    protected $categories = ['special'];

    protected $selectableInForms = false;

    protected function configFieldItems(): array
    {
        return [
            'from' => [
                'display' => __('From'),
                'type' => 'text',
                'instructions' => __('statamic::fieldtypes.slug.config.from'),
                'default' => 'title',
                'width' => 50,
            ],
            'generate' => [
                'display' => __('Generate'),
                'type' => 'toggle',
                'instructions' => __('statamic::fieldtypes.slug.config.generate'),
                'default' => true,
                'width' => 50,
            ],
        ];
    }
}
