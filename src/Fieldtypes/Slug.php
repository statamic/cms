<?php

namespace Statamic\Fieldtypes;

class Slug extends Text
{
    protected $categories = ['special'];

    protected $selectableInForms = false;

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Input Behavior'),
                'fields' => [
                    'from' => [
                        'display' => __('From'),
                        'type' => 'text',
                        'instructions' => __('statamic::fieldtypes.slug.config.from'),
                        'default' => 'title',
                    ],
                    'generate' => [
                        'display' => __('Generate'),
                        'type' => 'toggle',
                        'instructions' => __('statamic::fieldtypes.slug.config.generate'),
                        'default' => true,
                    ],
                    'show_regenerate' => [
                        'display' => __('Show Regenerate Button'),
                        'type' => 'toggle',
                        'instructions' => __('statamic::fieldtypes.slug.config.show_regenerate'),
                        'default' => false,
                        'width' => 50,
                    ],
                ],
            ],
        ];
    }
}
