<?php

namespace Statamic\Fieldtypes;

use Statamic\Fields\Fieldtype;

use function Statamic\trans as __;

class Info extends Fieldtype
{
    protected $categories = ['special'];
    protected $keywords = ['alert', 'message', 'notice', 'warning'];
    protected $icon = 'info';
    protected $localizable = false;
    protected $validatable = false;
    protected $defaultable = false;

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Content'),
                'fields' => [
                    'content' => [
                        'display' => __('Content'),
                        'instructions' => __('statamic::fieldtypes.info.config.content'),
                        'type' => 'textarea',
                    ],
                ],
            ],
            [
                'display' => __('Appearance'),
                'fields' => [
                    'state' => [
                        'display' => __('State'),
                        'instructions' => __('statamic::fieldtypes.info.config.state'),
                        'type' => 'select',
                        'default' => 'notice',
                        'options' => [
                            'notice' => __('Notice'),
                            'tip' => __('Tip'),
                            'warning' => __('Warning'),
                            'important' => __('Important Warning'),
                            'success' => __('Success'),
                        ],
                        'width' => 50,
                    ],
                    'icon' => [
                        'display' => __('Icon'),
                        'instructions' => __('statamic::fieldtypes.info.config.icon'),
                        'type' => 'icon',
                        'set' => 'default',
                        'mode' => 'compact',
                        'width' => 50,
                    ],
                ],
            ],
        ];
    }
}
