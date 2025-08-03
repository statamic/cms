<?php

namespace Statamic\Fieldtypes;

use Statamic\Facades\GraphQL;
use Statamic\Fields\Fieldtype;
use Statamic\Query\Scopes\Filters\Fields\Toggle as ToggleFilter;

class Toggle extends Fieldtype
{
    protected $categories = ['controls'];
    protected $keywords = ['checkbox', 'bool', 'boolean'];
    protected $selectableInForms = true;
    protected $defaultValue = false;

    protected function configFieldItems(): array
    {
        return [
            [
                'display' => __('Appearance'),
                'fields' => [
                    'inline_label' => [
                        'display' => __('Inline Label'),
                        'instructions' => __('statamic::fieldtypes.toggle.config.inline_label'),
                        'type' => 'text',
                        'default' => '',
                        'width' => '50',
                    ],
                    'inline_label_when_true' => [
                        'display' => __('Inline Label when True'),
                        'instructions' => __('statamic::fieldtypes.toggle.config.inline_label_when_true'),
                        'type' => 'text',
                        'default' => '',
                        'width' => '50',
                    ],
                ],
            ],
            [
                'display' => __('Data & Format'),
                'fields' => [
                    'default' => [
                        'display' => __('Default Value'),
                        'instructions' => __('statamic::messages.fields_default_instructions'),
                        'type' => 'toggle',
                        'width' => '50',
                    ],
                ],
            ],
        ];
    }

    public function preProcess($data)
    {
        return (bool) $data;
    }

    public function process($data)
    {
        if (is_null($data)) {
            return null;
        }

        return (bool) $data;
    }

    public function augment($data)
    {
        return (bool) $data;
    }

    public function toGqlType()
    {
        return GraphQL::boolean();
    }

    public function filter()
    {
        return new ToggleFilter($this);
    }
}
